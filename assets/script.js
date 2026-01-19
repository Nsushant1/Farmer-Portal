// Crop Management Portal - JavaScript

document.addEventListener("DOMContentLoaded", () => {
  console.log("CropManager Portal Loaded");

  // Initialize tooltips and interactive elements
  initializeDeleteConfirmations();
  initializeFormValidation();
  initializeNavigation();
});

// Delete confirmation dialogs
function initializeDeleteConfirmations() {
  const deleteButtons = document.querySelectorAll(".action-btn.delete");
  deleteButtons.forEach((button) => {
    button.addEventListener("click", (e) => {
      if (
        !confirm(
          "Are you sure you want to delete this item? This action cannot be undone."
        )
      ) {
        e.preventDefault();
      }
    });
  });
}

// Form validation
function initializeFormValidation() {
  const forms = document.querySelectorAll("form");
  forms.forEach((form) => {
    form.addEventListener("submit", (e) => {
      const requiredFields = form.querySelectorAll("[required]");
      let isValid = true;

      requiredFields.forEach((field) => {
        if (!field.value.trim()) {
          isValid = false;
          field.style.borderColor = "#f44336";
        } else {
          field.style.borderColor = "";
        }
      });

      if (!isValid) {
        e.preventDefault();
        alert("Please fill in all required fields");
      }
    });

    // Reset border color when user starts typing
    const inputs = form.querySelectorAll("input, select, textarea");
    inputs.forEach((input) => {
      input.addEventListener("focus", function () {
        this.style.borderColor = "";
      });
    });
  });
}

// Navigation active state
function initializeNavigation() {
  const currentPage = window.location.pathname;
  const navLinks = document.querySelectorAll(".nav-link");

  navLinks.forEach((link) => {
    if (
      link.href.includes(currentPage) ||
      (currentPage.includes("dashboard") && link.href.includes("dashboard")) ||
      (currentPage.includes("crops") && link.href.includes("crops")) ||
      (currentPage.includes("expenses") && link.href.includes("expenses")) ||
      (currentPage.includes("reports") && link.href.includes("reports"))
    ) {
      link.style.backgroundColor = "rgba(255, 255, 255, 0.2)";
    }
  });
}

// Utility function to format currency
function formatCurrency(amount) {
  return (
    "₹" +
    Number.parseFloat(amount)
      .toFixed(2)
      .replace(/\B(?=(\d{3})+(?!\d))/g, ",")
  );
}

// Utility function to format date
function formatDate(dateString) {
  const options = { year: "numeric", month: "short", day: "numeric" };
  return new Date(dateString).toLocaleDateString("en-US", options);
}

// Export data to CSV (alternative method)
function exportToCSV(tableId, filename) {
  const table = document.getElementById(tableId);
  const csv = [];

  // Get headers
  const headers = [];
  table.querySelectorAll("thead th").forEach((th) => {
    headers.push(th.textContent.trim());
  });
  csv.push(headers.join(","));

  // Get rows
  table.querySelectorAll("tbody tr").forEach((tr) => {
    const row = [];
    tr.querySelectorAll("td").forEach((td) => {
      row.push('"' + td.textContent.trim().replace(/"/g, '""') + '"');
    });
    csv.push(row.join(","));
  });

  // Download
  const csvContent = csv.join("\n");
  const blob = new Blob([csvContent], { type: "text/csv" });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = filename || "export.csv";
  a.click();
}
