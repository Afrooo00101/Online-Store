/* ============================= */
/* SIDEBAR */
/* ============================= */
const menuBtn = document.getElementById("menuBtn");
const sidebar = document.getElementById("sidebar");
const closeSidebar = document.getElementById("closeSidebar");

if (menuBtn) {
  menuBtn.onclick = () => sidebar.classList.add("active");
}

if (closeSidebar) {
  closeSidebar.onclick = () => sidebar.classList.remove("active");
}

/* ============================= */
/* DARK MODE */
/* ============================= */
const themeToggle = document.getElementById("themeToggle");

if (themeToggle) {
  themeToggle.onclick = () => {
    document.body.classList.toggle("dark-mode");
    localStorage.setItem(
      "theme",
      document.body.classList.contains("dark-mode") ? "dark" : "light"
    );
  };
}

// Load saved theme
if (localStorage.getItem("theme") === "dark") {
  document.body.classList.add("dark-mode");
};
// Image hover effect with mutation observer
function setupImageHover() {
    // Function to add hover to a single image
    function addHoverToImage(img) {
        if (!img.dataset.hover) return;
        
        const original = img.src;
        const hover = img.dataset.hover;
        
        // Mouse events
        img.addEventListener('mouseenter', () => img.src = hover);
        img.addEventListener('mouseleave', () => img.src = original);
        
        // Touch events
        let isTouched = false;
        img.addEventListener('touchstart', (e) => {
            e.preventDefault();
            img.src = isTouched ? original : hover;
            isTouched = !isTouched;
        });
    }
    
    // Apply to existing images
    document.querySelectorAll('img[data-hover]').forEach(addHoverToImage);
    
    // Watch for new images added dynamically
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === 1) { // Element node
                    if (node.matches && node.matches('img[data-hover]')) {
                        addHoverToImage(node);
                    }
                    if (node.querySelectorAll) {
                        node.querySelectorAll('img[data-hover]').forEach(addHoverToImage);
                    }
                }
            });
        });
    });
    
    // Start observing
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', setupImageHover);