
document.addEventListener('DOMContentLoaded', function() {
    const itemsPerPage = 6; // Set videos per page
    let currentPage = 1;
    const videoCards = document.querySelectorAll('.video-card');
    const totalPages = Math.ceil(videoCards.length / itemsPerPage);
    const paginationContainer = document.getElementById('pagination');

    function showPage(page) {
        let start = (page - 1) * itemsPerPage;
        let end = start + itemsPerPage;

        videoCards.forEach((card, index) => {
            card.style.display = (index >= start && index < end) ? '' : 'none';
        });

        updatePaginationButtons(page);
    }

    function updatePaginationButtons(page) {
        paginationContainer.innerHTML = '';

        if (totalPages > 1) {
            let prevDisabled = page === 1 ? 'w3-disabled' : '';
            let nextDisabled = page === totalPages ? 'w3-disabled' : '';

            paginationContainer.innerHTML += `<a href="#" class="w3-button w3-light-grey ${prevDisabled}" onclick="changePage(${page - 1})">&laquo;</a>`;

            for (let i = 1; i <= totalPages; i++) {
                let activeClass = i === page ? 'w3-green' : 'w3-light-grey';
                paginationContainer.innerHTML += `<a href="#" class="w3-button ${activeClass}" onclick="changePage(${i})">${i}</a>`;
            }

            paginationContainer.innerHTML += `<a href="#" class="w3-button w3-light-grey ${nextDisabled}" onclick="changePage(${page + 1})">&raquo;</a>`;
        }
    }

    window.changePage = function(page) {
        if (page < 1 || page > totalPages) return;
        currentPage = page;
        showPage(currentPage);
    }

    showPage(currentPage);
});

