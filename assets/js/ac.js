document.addEventListener('DOMContentLoaded', function () {
    document.getElementById("jddepartmentunit").addEventListener("change", function() {
        let deptUnitCode = this.value;

        fetch('parameter/parameter.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ deptunitcode: deptUnitCode })
        })
        .then(response => response.json())
        .then(data => {
            let positionsContainer = document.getElementById("jdposition");
            positionsContainer.innerHTML = ''; 
            if (data.positions && data.positions.length > 0) {
                data.positions.forEach(position => {
                    let option = document.createElement('option');
                    option.value = position.id;
                    option.text = position.poname;
                    positionsContainer.appendChild(option);
                });
            } else {
                positionsContainer.innerHTML = '<option>No positions found</option>';
            }
        })
        
        .catch(error => console.error('Error fetching positions:', error));
    });
});
