<?php
/**
 * ==========================================
 * ML-BASED RECOMMENDATION ENGINE
 * ==========================================
 * Machine Learning functions for:
 * 1. Content-Based Filtering
 * 2. Collaborative Filtering  
 * 3. Student Clustering (K-Means Logic)
 * ==========================================
 */

// ============================================
// 1. CONTENT-BASED FILTERING
// ============================================

/**
 * Get Content-Based Recommendations for a student
 * Based on: Category, Author, Rating, Popularity
 * 
 * @param PDO $dbh - Database connection
 * @param string $studentId - Student ID
 * @param int $limit - Number of recommendations (default: 3)
 * @return array - Recommended books with similarity scores
 */
function getContentBasedRecommendations($dbh, $studentId, $limit = 3) {
    // Get last issued book by student
    $sql_last_book = "SELECT b.* FROM tblbooks b 
                     INNER JOIN tblissuedbookdetails i ON b.id = i.BookId 
                     WHERE i.StudentID = :studentId 
                     ORDER BY i.IssuesDate DESC LIMIT 1";
    $query = $dbh->prepare($sql_last_book);
    $query->bindParam(':studentId', $studentId);
    $query->execute();
    $last_book = $query->fetch(PDO::FETCH_OBJ);
    
    if (!$last_book) {
        return array(); // No recommendation if no book issued
    }
    
    // Get all available books (excluding already issued)
    $sql_books = "SELECT b.*, 
                         (SELECT COUNT(*) FROM tblissuedbookdetails WHERE BookId = b.id) as popularity,
                         (SELECT AVG(Rating) FROM tblbookratings WHERE BookId = b.id) as avg_rating
                  FROM tblbooks b 
                  WHERE b.id != :bookId 
                  AND b.id NOT IN (
                      SELECT BookId FROM tblissuedbookdetails 
                      WHERE StudentID = :studentId AND (RetrunStatus IS NULL OR RetrunStatus = 0)
                  )
                  ORDER BY b.id";
    
    $query = $dbh->prepare($sql_books);
    $query->bindParam(':bookId', $last_book->id);
    $query->bindParam(':studentId', $studentId);
    $query->execute();
    $books = $query->fetchAll(PDO::FETCH_OBJ);
    
    $recommendations = array();
    
    foreach ($books as $book) {
        // Calculate Similarity Score
        $similarity_score = 0;
        
        // Feature 1: Category Match (Weight = 3)
        if ($book->CatId == $last_book->CatId) {
            $similarity_score += 30;
        }
        
        // Feature 2: Author Match (Weight = 2)
        if ($book->AuthorId == $last_book->AuthorId) {
            $similarity_score += 20;
        }
        
        // Feature 3: Rating Similarity (Weight = 1)
        $last_book_rating = getAverageRating($dbh, $last_book->id);
        $current_book_rating = $book->avg_rating ? $book->avg_rating : getAverageRating($dbh, $book->id);
        
        $rating_diff = abs($last_book_rating - $current_book_rating);
        if ($rating_diff <= 1.0) {
            $similarity_score += 10 - ($rating_diff * 5);
        }
        
        // Feature 4: Popularity (Weight = 1)
        $popularity = $book->popularity ? $book->popularity : 0;
        $max_popularity = getMaxPopularity($dbh);
        if ($max_popularity > 0) {
            $popularity_score = ($popularity / $max_popularity) * 10;
            $similarity_score += $popularity_score;
        }
        
        // Only add if similarity score > 10
        if ($similarity_score > 10) {
            $book->similarity_score = round($similarity_score, 2);
            $recommendations[] = $book;
        }
    }
    
    // Sort by similarity score (descending)
    usort($recommendations, function($a, $b) {
        return $b->similarity_score - $a->similarity_score;
    });
    
    return array_slice($recommendations, 0, $limit);
}

/**
 * Get Average Rating for a Book
 * @param PDO $dbh
 * @param int $bookId
 * @return float
 */
function getAverageRating($dbh, $bookId) {
    $sql = "SELECT AVG(Rating) as avg_rating FROM tblbookratings WHERE BookId = :bookId";
    $query = $dbh->prepare($sql);
    $query->bindParam(':bookId', $bookId, PDO::PARAM_INT);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);
    return $result && $result->avg_rating ? (float)$result->avg_rating : 0;
}

/**
 * Get Maximum Popularity Score (for normalization)
 * @param PDO $dbh
 * @return int
 */
function getMaxPopularity($dbh) {
    $sql = "SELECT MAX(issue_count) as max_count FROM (
            SELECT COUNT(*) as issue_count FROM tblissuedbookdetails 
            GROUP BY BookId) as popularity";
    $query = $dbh->prepare($sql);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);
    return $result && $result->max_count ? (int)$result->max_count : 0;
}

// ============================================
// 2. COLLABORATIVE FILTERING
// ============================================

/**
 * Get Collaborative Filtering Recommendations
 * "Users who borrowed this book also borrowed..."
 * 
 * @param PDO $dbh
 * @param string $studentId
 * @param int $limit
 * @return array - Recommended books based on co-occurrence
 */
function getCollaborativeFilteringRecommendations($dbh, $studentId, $limit = 3) {
    // Get books borrowed by current student
    $sql_student_books = "SELECT DISTINCT BookId FROM tblissuedbookdetails 
                          WHERE StudentID = :studentId";
    $query = $dbh->prepare($sql_student_books);
    $query->bindParam(':studentId', $studentId);
    $query->execute();
    $student_books = $query->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($student_books)) {
        return array();
    }
    
    // Convert to string for SQL IN clause
    $books_str = implode(',', $student_books);
    
    // Find other students who borrowed same books
    $sql_similar_students = "SELECT DISTINCT StudentID FROM tblissuedbookdetails 
                            WHERE BookId IN ($books_str) AND StudentID != :studentId";
    $query = $dbh->prepare($sql_similar_students);
    $query->bindParam(':studentId', $studentId);
    $query->execute();
    $similar_students = $query->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($similar_students)) {
        return array();
    }
    
    // Find books borrowed by similar students (co-occurrence)
    $students_str = "'" . implode("','", $similar_students) . "'";
    
    $sql_cooccurrence = "SELECT b.*, COUNT(*) as co_occurrence_count
                        FROM tblbooks b
                        INNER JOIN tblissuedbookdetails i ON b.id = i.BookId
                        WHERE i.StudentID IN ($students_str) 
                        AND b.id NOT IN ($books_str)
                        GROUP BY b.id
                        ORDER BY co_occurrence_count DESC
                        LIMIT :limit";
    
    $query = $dbh->prepare($sql_cooccurrence);
    $query->bindParam(':limit', $limit, PDO::PARAM_INT);
    $query->execute();
    $recommendations = $query->fetchAll(PDO::FETCH_OBJ);
    
    return $recommendations;
}

// ============================================
// 3. K-MEANS CLUSTERING (LOGIC-BASED)
// ============================================

/**
 * Normalize Value between 0 and 1
 * @param float $value
 * @param float $min
 * @param float $max
 * @return float
 */
function normalizeValue($value, $min, $max) {
    if ($max == $min) return 0;
    return ($value - $min) / ($max - $min);
}

/**
 * Get Student Clustering Features
 * Features: Books Issued, Avg Rating, Late Returns
 * 
 * @param PDO $dbh
 * @param string $studentId
 * @return array - [books_issued, avg_rating, late_returns]
 */
function getStudentFeatures($dbh, $studentId) {
    // Feature 1: Number of books issued
    $sql_books = "SELECT COUNT(*) as count FROM tblissuedbookdetails WHERE StudentID = :studentId";
    $query = $dbh->prepare($sql_books);
    $query->bindParam(':studentId', $studentId);
    $query->execute();
    $books_issued = (int)$query->fetch(PDO::FETCH_OBJ)->count;
    
    // Feature 2: Average rating given
    $sql_rating = "SELECT AVG(CAST(Rating as DECIMAL(10,2))) as avg_rating FROM tblbookratings 
                   WHERE StudentId = :studentId";
    $query = $dbh->prepare($sql_rating);
    $query->bindParam(':studentId', $studentId);
    $query->execute();
    $avg_rating = (float)($query->fetch(PDO::FETCH_OBJ)->avg_rating ?? 0);
    
    // Feature 3: Late returns count
    $sql_late = "SELECT COUNT(*) as count FROM tblissuedbookdetails 
                 WHERE StudentID = :studentId AND ReturnDate > DATE_ADD(IssuesDate, INTERVAL 14 DAY)";
    $query = $dbh->prepare($sql_late);
    $query->bindParam(':studentId', $studentId);
    $query->execute();
    $late_returns = (int)$query->fetch(PDO::FETCH_OBJ)->count;
    
    return array(
        'books_issued' => $books_issued,
        'avg_rating' => $avg_rating,
        'late_returns' => $late_returns
    );
}

/**
 * Assign Student to Cluster
 * Cluster 1: Active Readers (high books_issued, high rating, low late_returns)
 * Cluster 2: Average Readers (medium activity)
 * Cluster 3: Inactive Users (low books_issued, low engagement)
 * 
 * @param PDO $dbh
 * @param string $studentId
 * @return int - Cluster number (1, 2, or 3)
 */
function getStudentCluster($dbh, $studentId) {
    // Get normalization bounds
    $sql_bounds = "SELECT 
                   MAX(COUNT(*)) as max_books,
                   MIN(COUNT(*)) as min_books
                   FROM tblissuedbookdetails
                   GROUP BY StudentID";
    
    // Get all students' features for normalization
    $sql_all_students = "SELECT DISTINCT StudentID FROM tblstudents WHERE Status = 1";
    $query = $dbh->prepare($sql_all_students);
    $query->execute();
    $all_students = $query->fetchAll(PDO::FETCH_COLUMN);
    
    $all_features = array();
    foreach ($all_students as $sid) {
        $all_features[$sid] = getStudentFeatures($dbh, $sid);
    }
    
    // Get bounds for normalization
    $max_books = max(array_column($all_features, 'books_issued'));
    $min_books = min(array_column($all_features, 'books_issued'));
    $max_rating = 5; // Max rating is always 5
    $min_rating = 0;
    $max_late = max(array_column($all_features, 'late_returns')) ?: 1;
    $min_late = 0;
    
    // Get current student's features
    $current_features = getStudentFeatures($dbh, $studentId);
    
    // Normalize features
    $norm_books = normalizeValue($current_features['books_issued'], $min_books, $max_books);
    $norm_rating = normalizeValue($current_features['avg_rating'], $min_rating, $max_rating);
    $norm_late = normalizeValue($current_features['late_returns'], $min_late, $max_late);
    
    // Define cluster centroids (arbitrary but logical)
    // Cluster 1: Active (1.0, 0.8, 0.2) - high books, high rating, low late
    // Cluster 2: Average (0.5, 0.5, 0.5) - medium all
    // Cluster 3: Inactive (0.2, 0.3, 0.8) - low books, low rating, high late
    
    $centroids = array(
        1 => array('books' => 1.0, 'rating' => 0.8, 'late' => 0.1), // Active
        2 => array('books' => 0.5, 'rating' => 0.5, 'late' => 0.5), // Average
        3 => array('books' => 0.2, 'rating' => 0.3, 'late' => 0.8)  // Inactive
    );
    
    // Calculate Euclidean distance to each centroid
    $distances = array();
    foreach ($centroids as $cluster_id => $centroid) {
        $distance = sqrt(
            pow($norm_books - $centroid['books'], 2) +
            pow($norm_rating - $centroid['rating'], 2) +
            pow($norm_late - $centroid['late'], 2)
        );
        $distances[$cluster_id] = $distance;
    }
    
    // Assign to cluster with minimum distance
    $assigned_cluster = array_keys($distances, min($distances))[0];
    
    return $assigned_cluster;
}

/**
 * Get Cluster Label
 * @param int $cluster - Cluster number
 * @return string - Cluster name
 */
function getClusterLabel($cluster) {
    $labels = array(
        1 => 'Active Reader',
        2 => 'Average Reader',
        3 => 'Inactive User'
    );
    return isset($labels[$cluster]) ? $labels[$cluster] : 'Unknown';
}

/**
 * Get Cluster Description
 * @param int $cluster
 * @return string
 */
function getClusterDescription($cluster) {
    $descriptions = array(
        1 => 'You are an excellent reader! You actively borrow books, give high ratings, and return them on time.',
        2 => 'You are a regular reader with moderate engagement. Consider exploring more categories!',
        3 => 'You are not very active. We recommend exploring our collection and joining reading programs!'
    );
    return isset($descriptions[$cluster]) ? $descriptions[$cluster] : '';
}

// ============================================
// 4. CLUSTER-BASED RECOMMENDATIONS
// ============================================

/**
 * Get Cluster-Based Recommendations
 * Recommend popular books within the student's cluster category
 * 
 * @param PDO $dbh
 * @param int $cluster - Student cluster
 * @param int $limit
 * @return array
 */
function getClusterBasedRecommendations($dbh, $cluster, $limit = 3) {
    // Define recommendations strategy based on cluster
    switch ($cluster) {
        case 1: // Active Readers
            // Recommend highly rated books
            $sql = "SELECT b.*, AVG(r.Rating) as avg_rating, COUNT(i.id) as popularity
                    FROM tblbooks b
                    LEFT JOIN tblbookratings r ON b.id = r.BookId
                    LEFT JOIN tblissuedbookdetails i ON b.id = i.BookId
                    GROUP BY b.id
                    HAVING avg_rating >= 3.5
                    ORDER BY avg_rating DESC, popularity DESC
                    LIMIT :limit";
            break;
            
        case 2: // Average Readers
            // Recommend trending books (moderate popularity and rating)
            $sql = "SELECT b.*, COUNT(i.id) as popularity, AVG(r.Rating) as avg_rating
                    FROM tblbooks b
                    LEFT JOIN tblissuedbookdetails i ON b.id = i.BookId
                    LEFT JOIN tblbookratings r ON b.id = r.BookId
                    GROUP BY b.id
                    ORDER BY popularity DESC, avg_rating DESC
                    LIMIT :limit";
            break;
            
        case 3: // Inactive Users
            // Recommend popular books (to encourage participation)
            $sql = "SELECT b.*, COUNT(i.id) as popularity
                    FROM tblbooks b
                    LEFT JOIN tblissuedbookdetails i ON b.id = i.BookId
                    GROUP BY b.id
                    ORDER BY popularity DESC
                    LIMIT :limit";
            break;
            
        default:
            return array();
    }
    
    $query = $dbh->prepare($sql);
    $query->bindParam(':limit', $limit, PDO::PARAM_INT);
    $query->execute();
    return $query->fetchAll(PDO::FETCH_OBJ);
}

// ============================================
// 5. UPDATE STUDENT CLUSTER IN DATABASE
// ============================================

/**
 * Update Student's Cluster Assignment
 * @param PDO $dbh
 * @param string $studentId
 */
function updateStudentCluster($dbh, $studentId) {
    $cluster = getStudentCluster($dbh, $studentId);
    
    // Check if column exists first
    $sql_check = "SHOW COLUMNS FROM tblstudents LIKE 'student_cluster'";
    $query = $dbh->prepare($sql_check);
    $query->execute();
    
    if ($query->rowCount() > 0) {
        $sql = "UPDATE tblstudents SET student_cluster = :cluster WHERE StudentId = :studentId";
        $query = $dbh->prepare($sql);
        $query->bindParam(':cluster', $cluster, PDO::PARAM_INT);
        $query->bindParam(':studentId', $studentId);
        $query->execute();
    }
}

/**
 * Get Student Cluster from Database
 * @param PDO $dbh
 * @param string $studentId
 * @return int
 */
function getStudentClusterFromDB($dbh, $studentId) {
    // Try to get from database first
    $sql = "SELECT student_cluster FROM tblstudents WHERE StudentId = :studentId";
    $query = $dbh->prepare($sql);
    $query->bindParam(':studentId', $studentId);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);
    
    if ($result && !is_null($result->student_cluster)) {
        return (int)$result->student_cluster;
    }
    
    // If not in database, calculate and update
    return getStudentCluster($dbh, $studentId);
}

// ============================================
// 6. SUMMARY STATISTICS
// ============================================

/**
 * Get Summary Statistics for Dashboard
 * @param PDO $dbh
 * @param string $studentId
 * @return array
 */
function getStudentSummary($dbh, $studentId) {
    $features = getStudentFeatures($dbh, $studentId);
    $cluster = getStudentClusterFromDB($dbh, $studentId);
    
    return array(
        'books_issued' => $features['books_issued'],
        'avg_rating' => round($features['avg_rating'], 2),
        'late_returns' => $features['late_returns'],
        'cluster' => $cluster,
        'cluster_label' => getClusterLabel($cluster),
        'cluster_description' => getClusterDescription($cluster)
    );
}

?>
