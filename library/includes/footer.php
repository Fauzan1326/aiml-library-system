<div class="footer-section">

    <div class="quote-wrapper">
        <div id="quoteTrack"></div>
    </div>

</div>

<style>
.quote-wrapper {
    width: 100%;
    overflow: hidden;
    background: #f4f6ff;
    padding: 10px 0;
}

#quoteTrack {
    white-space: nowrap;
    font-size: 17px;
    font-style: italic;
    color: #1a237e;
    animation: scrollQuote 18s linear infinite;
}

@keyframes scrollQuote {
    from { transform: translateX(100%); }
    to   { transform: translateX(-100%); }
}
</style>

<script>
const quotes = [
    "“Education is not the learning of facts, but the training of the mind to think.” – Albert Einstein",
    "“An investment in knowledge pays the best interest.” – Benjamin Franklin",
    "“The beautiful thing about learning is nobody can take it away from you.” – B.B. King",
    "“Learning never exhausts the mind.” – Leonardo da Vinci",
    "“Success is the sum of small efforts repeated daily.” – Robert Collier",
    "“Knowledge is power.” – Francis Bacon"
];

let index = 0;
const quoteTrack = document.getElementById("quoteTrack");

function changeQuote() {
    quoteTrack.textContent = quotes[index];
    index = (index + 1) % quotes.length;
}

changeQuote();
setInterval(changeQuote, 18000);
</script>
