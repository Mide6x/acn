document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("checklistForm");

    // Load saved checklist state
    const savedChecklist = JSON.parse(localStorage.getItem("checklistState"));
    if (savedChecklist) {
        savedChecklist.forEach((itemId) => {
            const checkbox = document.getElementById(itemId);
            if (checkbox) {
                checkbox.checked = true;
            }
        });
    }

    // Save checklist state on submit
    form.addEventListener("submit", function(event) {
        event.preventDefault(); // Prevent form submission if using PHP to save

        const checkedItems = Array.from(document.querySelectorAll('input[type="checkbox"]:checked'))
            .map(checkbox => checkbox.id);

        // Save state to localStorage
        localStorage.setItem("checklistState", JSON.stringify(checkedItems));

        alert(JSON.stringify(checkedItems));
    });
});
