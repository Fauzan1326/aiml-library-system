-- =====================================================
-- ML-BASED LIBRARY MANAGEMENT SYSTEM - DATABASE UPDATES
-- =====================================================
-- Add student_cluster column to tblstudents table
-- Cluster 1: Active Readers
-- Cluster 2: Average Readers
-- Cluster 3: Inactive Users
-- =====================================================

-- Add the student_cluster column if it doesn't exist
ALTER TABLE `tblstudents` ADD COLUMN `student_cluster` INT(1) DEFAULT NULL AFTER `UpdationDate`;

-- Add index for faster queries
ALTER TABLE `tblstudents` ADD INDEX `idx_student_cluster` (`student_cluster`);

-- Create a view for easy recommendation queries
CREATE OR REPLACE VIEW vw_student_engagement AS
SELECT 
    s.StudentId,
    s.FullName,
    COUNT(DISTINCT i.id) as books_issued,
    COALESCE(AVG(r.Rating), 0) as avg_rating_given,
    COALESCE(SUM(CASE WHEN i.ReturnDate > DATE_ADD(i.IssuesDate, INTERVAL 14 DAY) THEN 1 ELSE 0 END), 0) as late_returns
FROM tblstudents s
LEFT JOIN tblissuedbookdetails i ON s.StudentId = i.StudentID
LEFT JOIN tblbookratings r ON s.StudentId = r.StudentId
WHERE s.Status = 1
GROUP BY s.StudentId, s.FullName;

-- Create a view for book popularity and ratings
CREATE OR REPLACE VIEW vw_book_popularity AS
SELECT 
    b.id,
    b.BookName,
    b.CatId,
    b.AuthorId,
    COUNT(DISTINCT i.StudentID) as unique_borrowers,
    COUNT(i.id) as total_issues,
    COALESCE(AVG(r.Rating), 0) as avg_rating,
    COUNT(r.id) as rating_count
FROM tblbooks b
LEFT JOIN tblissuedbookdetails i ON b.id = i.BookId
LEFT JOIN tblbookratings r ON b.id = r.BookId
GROUP BY b.id, b.BookName, b.CatId, b.AuthorId;

-- Create a view for co-occurrence (collaborative filtering)
CREATE OR REPLACE VIEW vw_book_cooccurrence AS
SELECT 
    i1.BookId as book1_id,
    i2.BookId as book2_id,
    COUNT(*) as cooccurrence_count
FROM tblissuedbookdetails i1
INNER JOIN tblissuedbookdetails i2 ON i1.StudentID = i2.StudentID
WHERE i1.BookId < i2.BookId
GROUP BY i1.BookId, i2.BookId
HAVING cooccurrence_count > 0
ORDER BY cooccurrence_count DESC;
