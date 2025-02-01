<div class="accessibility-toolbar" style="background: #f8f9fa; padding: 10px; position: sticky; top: 0; z-index: 1000;">
    <div class="container d-flex justify-content-end gap-2">
        <!-- Text Size Controls -->
        <button onclick="changeFontSize('increase')" class="btn btn-sm btn-outline-primary" aria-label="Increase text size">
            <i class="bi bi-zoom-in"></i> A+
        </button>
        <button onclick="changeFontSize('decrease')" class="btn btn-sm btn-outline-primary" aria-label="Decrease text size">
            <i class="bi bi-zoom-out"></i> A-
        </button>
        
        <!-- High Contrast Toggle -->
        <button onclick="toggleHighContrast()" class="btn btn-sm btn-outline-dark" aria-label="Toggle high contrast">
            <i class="bi bi-circle-half"></i> Contrast
        </button>

        <!-- Screen Reader Text -->
        <button onclick="toggleScreenReaderText()" class="btn btn-sm btn-outline-info" aria-label="Toggle screen reader hints">
            <i class="bi bi-eye"></i> Screen Reader Hints
        </button>
    </div>
</div>

<script>
// Font size control
let currentFontSize = 100;
function changeFontSize(direction) {
    if (direction === 'increase' && currentFontSize < 150) {
        currentFontSize += 10;
    } else if (direction === 'decrease' && currentFontSize > 70) {
        currentFontSize -= 10;
    }
    document.body.style.fontSize = `${currentFontSize}%`;
    localStorage.setItem('fontSize', currentFontSize);
}

// High contrast mode
function toggleHighContrast() {
    document.body.classList.toggle('high-contrast');
    localStorage.setItem('highContrast', document.body.classList.contains('high-contrast'));
}

// Screen reader text toggle
function toggleScreenReaderText() {
    document.body.classList.toggle('show-sr-text');
    localStorage.setItem('showSrText', document.body.classList.contains('show-sr-text'));
}

// Load user preferences
document.addEventListener('DOMContentLoaded', () => {
    // Restore font size
    const savedFontSize = localStorage.getItem('fontSize');
    if (savedFontSize) {
        currentFontSize = parseInt(savedFontSize);
        document.body.style.fontSize = `${currentFontSize}%`;
    }

    // Restore contrast setting
    if (localStorage.getItem('highContrast') === 'true') {
        document.body.classList.add('high-contrast');
    }

    // Restore screen reader text setting
    if (localStorage.getItem('showSrText') === 'true') {
        document.body.classList.add('show-sr-text');
    }
});
</script>