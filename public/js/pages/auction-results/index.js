/**
 * Auction Results Page - Filters Handler
 */

/**
 * Handle posts per page selector change
 * Uses event delegation to handle dynamically replaced elements
 */
function handlePerPageChange() {
  // Event delegation - el listener permanece aunque el elemento sea reemplazado
  document.addEventListener("change", function (e) {
    console.log("Change event detected:", e.target);
    if (e.target && e.target.id === "blog-perpage") {
      const url = new URL(window.location.href);
      url.searchParams.set("posts_per_page", e.target.value);
      // Reset pagination to page 1 when changing items per page
      url.searchParams.delete("paged");
      url.searchParams.delete("page");
      window.location.href = url.toString();
    }
  });
}

/**
 * Initialize all auction results page functionality
 */
export function initAuctionResultsPage() {
  handlePerPageChange();
}
