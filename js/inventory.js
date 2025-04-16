// inventory.js

// Loads the "Add Action" form dynamically via AJAX
function loadAddAction() {
    fetch('ajax_add_action.php')
        .then(response => response.text())
        .then(html => {
            document.getElementById('contentSection').innerHTML = html;
        })
        .catch(err => console.error('Error loading form:', err));
}

// Renders dynamic form fields based on selected action
function renderDynamicFields() {
    const actionType = document.getElementById('action_type').value;
    let fieldsHtml = '';

    const today = new Date().toISOString().split('T')[0];
    const nowTime = new Date().toTimeString().split(' ')[0].substring(0,5);

    fieldsHtml += `
        <label>Date:</label>
        <input type="date" id="action_date" name="action_date" value="${today}">
        <label>Time:</label>
        <input type="time" id="action_time" name="action_time" value="${nowTime}">
        <label>Order #:</label>
        <input type="text" id="order_number" name="order_number">
        <label>Job Name:</label>
        <input type="text" id="job_name" name="job_name">
    `;

    if (actionType === 'Handling In' || actionType === 'Handling Out') {
        fieldsHtml += `
            <label>Type:</label>
            <select name="type">
                <option>Pallets</option>
                <option>Cartons</option>
            </select>
            <label>Quantity:</label>
            <input type="number" name="quantity">
            <label>Weight (lbs):</label>
            <input type="number" step="0.01" name="weight">
        `;
    } else if (actionType === 'Open Inspect') {
        fieldsHtml += `
            <label>Type:</label>
            <select name="type">
                <option>Pallets</option>
                <option>Cartons</option>
            </select>
            <label>Hours:</label>
            <input type="number" step="0.01" name="hours">
        `;
    }

    document.getElementById('dynamic_fields').innerHTML = fieldsHtml;

    // Attach blur event for job name fetching
    const orderField = document.getElementById('order_number');
    if (orderField) {
        orderField.addEventListener('blur', fetchJobName);
    }
}

// Fetches and inserts the current month table
function loadCurrentMonth() {
    const chosenMonth = document.getElementById('monthDropdown').value;
    const url = 'ajax_view_month_content.php?month=' + encodeURIComponent(chosenMonth);

    fetch(url)
      .then(response => response.text())
      .then(html => {
          document.getElementById('contentSection').innerHTML = html;
          
          // Sort by Job Name (col 1) ascending after load
          setTimeout(() => {
              if (typeof window.sortTable === 'function') {
                  window.sortTable(1, false);
              }
          }, 50);
      })
      .catch(err => console.error('Error:', err));
}

function loadMaintenance() {
    fetch('ajax_maintenance.php')
        .then(response => response.text())
        .then(html => {
            document.getElementById('contentSection').innerHTML = html;
        });
}

function loadActionMaintenance() {
    fetch('ajax_action_maintenance.php')
        .then(response => response.text())
        .then(html => {
            document.getElementById('maintenanceContent').innerHTML = html;
        });
}

function loadProjectMaintenance() {
    fetch('ajax_project_maintenance.php')
        .then(response => response.text())
        .then(html => {
            document.getElementById('maintenanceContent').innerHTML = html;
        });
}

function saveActionUpdate(id, field, value) {
    const data = new FormData();
    data.append('id', id);
    data.append('field', field);
    data.append('value', value);

    fetch('save_action_update.php', {method: 'POST', body: data})
        .then(resp => resp.json())
        .then(resp => alert(resp.message));
}

function saveProjectRates(id) {
    const formData = new FormData(document.getElementById(`rateForm-${id}`));

    fetch('save_project_rates.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response error');
        return response.json();
    })
    .then(data => {
        alert(data.message);
    })
    .catch(err => {
        console.error('AJAX error:', err);
        alert('An error occurred while updating the rates.');
    });
}

// Fetch job name based on entered order number
function fetchJobName() {
    const orderNumber = document.getElementById('order_number').value;

    if (orderNumber.trim() === '') {
        document.getElementById('job_name').value = '';
        return;
    }

    const formData = new FormData();
    formData.append('order_number', orderNumber);

    fetch('ajax_fetch_jobname.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.job_name) {
            document.getElementById('job_name').value = data.job_name;
        } else {
            document.getElementById('job_name').value = '';
        }
    })
    .catch(err => {
        console.error('Error fetching job name:', err);
    });
}

// Loads the Delete Projects view
function loadDeleteProjects() {
    fetch('ajax_delete_projects.php')
        .then(response => response.text())
        .then(html => {
            document.getElementById('maintenanceContent').innerHTML = html;
        })
        .catch(err => console.error('Error loading delete projects view:', err));
}

// Deletes a project from inv_rates
function deleteProject(projectId) {
    if (!confirm("Are you sure you want to delete this project? This action cannot be undone.")) {
        return;
    }

    const formData = new FormData();
    formData.append('id', projectId);

    fetch('delete_project.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) throw new Error("Network error");
        return response.json();
    })
    .then(data => {
        alert(data.message);
        loadDeleteProjects();
    })
    .catch(err => {
        console.error("Error deleting project:", err);
        alert("An error occurred while deleting the project.");
    });
}

// Submits the action form
function submitAction() {
    const formData = new FormData(document.getElementById('actionForm'));

    fetch('submit_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.status === 'success') {
            document.getElementById('actionForm').reset();
            document.getElementById('dynamic_fields').innerHTML = '';
        }
    })
    .catch(err => {
        console.error('AJAX submission error:', err);
        alert('Error submitting form.');
    });
}


// This function loads the month selection partial for the “View by Month” feature
function loadViewMonth() {
    fetch('view_month_index_content.php')
        .then(response => response.text())
        .then(html => {
            document.getElementById('contentSection').innerHTML = html;

            const monthSelect = document.getElementById('month');
            if (monthSelect) {
                monthSelect.addEventListener('change', loadSelectedMonthData);
                loadSelectedMonthData(); // initial load
            }
        })
        .catch(err => console.error('Error loading View by Month partial:', err));
}

// Loads the partial that shows the monthly data, then sorts by col 1 (job name) asc
function loadSelectedMonthData() {
    const monthSelect = document.getElementById('month');
    const selectedMonth = monthSelect.value;
    const url = 'ajax_view_month_content.php?month=' + encodeURIComponent(selectedMonth);

    fetch(url)
        .then(response => response.text())
        .then(html => {
            document.getElementById('monthContentArea').innerHTML = html;

            // Default sort by job name asc after the partial is inserted
            setTimeout(() => {
                if (typeof window.sortTable === 'function') {
                    window.sortTable(1, false);
                }
            }, 50);
        })
        .catch(err => console.error('Error loading monthly data:', err));
}


// Yearly totals

function loadYearlyTotals() {
    fetch('ajax_yearly_totals.php')
        .then(response => response.text())
        .then(html => {
            document.getElementById('contentSection').innerHTML = html;

            const yearDropdown = document.getElementById('yearSelect');
            if (yearDropdown) {
                yearDropdown.addEventListener('change', loadYearlyData);
                loadYearlyData(); // default load
            }
        })
        .catch(err => console.error('Error loading yearly totals page:', err));
}

function loadYearlyData() {
    const selectedYear = document.getElementById('yearSelect').value;
    const url = 'ajax_yearly_totals_data.php?year=' + encodeURIComponent(selectedYear);

    fetch(url)
        .then(resp => resp.text())
        .then(html => {
            document.getElementById('yearlyTotalsContainer').innerHTML = html;
        })
        .catch(err => console.error('Error loading monthly aggregator:', err));
}


// --------------------------------------------------------------------
// Below are the global sorting functions for the monthly table 
// and the detailed actions table (Option A).
// --------------------------------------------------------------------
window.columnDirectionsMain = [];
window.columnDirectionsActions = [];

window.sortTable = function(n, toggle=true) {
    const table = document.getElementById('mainRevenueTable');
    if (!table) return;

    // Initialize directions array if empty
    if (window.columnDirectionsMain.length < table.rows[0].cells.length) {
        for (let i=0; i<table.rows[0].cells.length; i++) {
            window.columnDirectionsMain[i] = 'asc';
        }
    }

    let switching = true,
        rows = table.rows,
        shouldSwitch,
        i,
        dir = window.columnDirectionsMain[n],
        switchcount = 0;

    // If toggle is false => always 'asc'
    // otherwise flip asc <-> desc
    if (!toggle) {
        dir = 'asc';
    } else {
        dir = (dir === 'asc') ? 'desc' : 'asc';
    }
    window.columnDirectionsMain[n] = dir;

    // Clear old arrows
    window.clearArrows(table.tHead);

    // Put arrow
    let arrow = (dir === 'asc') ? '▲' : '▼';
    table.tHead.rows[0].cells[n].querySelector('.arrowSpan').textContent = arrow;

    while (switching) {
        switching = false;
        for (i=1; i<(rows.length-1); i++) {
            shouldSwitch = false;
            let x = rows[i].getElementsByTagName('TD')[n];
            let y = rows[i+1].getElementsByTagName('TD')[n];
            if (!x || !y) continue;
            let xVal = x.innerText.toLowerCase();
            let yVal = y.innerText.toLowerCase();

            if (dir==='asc' && xVal>yVal) {
                shouldSwitch = true; break;
            } else if (dir==='desc' && xVal<yVal) {
                shouldSwitch = true; break;
            }
        }
        if (shouldSwitch) {
            rows[i].parentNode.insertBefore(rows[i+1], rows[i]);
            switching = true;
            switchcount++;
        } else if (switchcount===0 && dir==='asc') {
            // If no switching done and direction is asc, break
            break;
        }
    }
};

window.sortActions = function(n, toggle=true) {
    const table = document.getElementById('actionsTable');
    if (!table) return;

    if (window.columnDirectionsActions.length < table.rows[0].cells.length) {
        for (let i=0; i<table.rows[0].cells.length; i++) {
            window.columnDirectionsActions[i] = 'asc';
        }
    }

    let switching = true,
        rows = table.rows,
        dir = window.columnDirectionsActions[n],
        switchcount = 0;

    if (!toggle) {
        dir = 'asc';
    } else {
        dir = (dir==='asc') ? 'desc' : 'asc';
    }
    window.columnDirectionsActions[n] = dir;

    // Clear old arrows
    window.clearArrows(table.tHead);

    let arrow = (dir === 'asc') ? '▲' : '▼';
    table.tHead.rows[0].cells[n].querySelector('.arrowSpan').textContent = arrow;

    while (switching) {
        switching = false;
        for (let i=1; i<(rows.length-1); i++) {
            let shouldSwitch = false;
            let x = rows[i].getElementsByTagName('TD')[n];
            let y = rows[i+1].getElementsByTagName('TD')[n];
            if (!x || !y) continue;
            let xVal = x.innerText.toLowerCase();
            let yVal = y.innerText.toLowerCase();

            if (dir==='asc' && xVal>yVal) {
                shouldSwitch = true; break;
            } else if (dir==='desc' && xVal<yVal) {
                shouldSwitch = true; break;
            }
        }
        if (shouldSwitch) {
            rows[i].parentNode.insertBefore(rows[i+1], rows[i]);
            switching = true;
            switchcount++;
        } else if (switchcount===0 && dir==='asc') {
            break;
        }
    }
};

window.clearArrows = function(thead) {
    let spans = thead.getElementsByClassName('arrowSpan');
    for (let span of spans) {
        span.textContent = '';
    }
};

                                                  
function exportMonthPDF() {
    const table = document.getElementById('mainRevenueTable');
    if (!table) {
        alert('No table found to export.');
        return;
    }

    // Gather rows in the current (sorted) DOM order
    const bodyRows = table.tBodies[0].rows;
    const rowData = [];
    for (let i = 0; i < bodyRows.length; i++) {
        const cells = bodyRows[i].cells;
        rowData.push({
            orderNumber: cells[0].innerText.trim(),
            jobName:     cells[1].innerText.trim(),
            startMonth:  cells[2].innerText.trim(),
            inMonth:     cells[3].innerText.trim(),
            outMonth:    cells[4].innerText.trim(),
            endMonth:    cells[5].innerText.trim(),
            openInspect: cells[6].innerText.trim(),
            total:       cells[7].innerText.trim(),
        });
    }

    // Grab the Grand Total from <tfoot>
    let grandTotal = '';
    if (table.tFoot && table.tFoot.rows.length > 0) {
        const footRow  = table.tFoot.rows[0];
        const footCell = footRow.cells[footRow.cells.length - 1];
        grandTotal = footCell ? footCell.innerText.trim() : '';
    }

    // Example: <h2>Monthly Storage & Cost View for April 2025</h2>
    // Then monthHeading might be "Monthly Storage & Cost View for April 2025"
    const monthHeading = document.querySelector('#monthContentArea h2');
    let monthTitle = monthHeading ? monthHeading.innerText.trim() : 'Monthly View';

    // See what we got:
    console.log('DEBUG: monthTitle from <h2> =', monthTitle);

    // Build payload
    const payload = {
        monthTitle: monthTitle,
        rows: rowData,
        grandTotal: grandTotal
    };

    // Post to export_pdf.php
    fetch('export_pdf.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not OK');
        return response.blob();
    })
    .then(blob => {
        const pdfUrl = URL.createObjectURL(blob);
        window.open(pdfUrl, '_blank');
        setTimeout(() => URL.revokeObjectURL(pdfUrl), 60000);
    })
    .catch(err => {
        console.error('Error exporting PDF:', err);
        alert('Failed to export PDF.');
    });
}


// inventory.js

function loadModifyDBEntries(page = 1) {
    fetch('ajax_modify_db_entries.php?page=' + page)
    .then(response => response.text())
    .then(html => {
        document.getElementById('maintenanceContent').innerHTML = html;
    })
    .catch(err => console.error('Error loading DB entries:', err));
}

// Called when user clicks "Save" in a row
function saveDBEntry(rowId) {
    // gather the updated data from that row's inputs
    const prefix = `row-${rowId}-`; // to identify each input
    // Suppose columns: action_type, action_date, action_time, order_number, job_name, type, quantity, weight, hours
    const action_type  = document.getElementById(prefix + 'action_type').value;
    const action_date  = document.getElementById(prefix + 'action_date').value;
    const action_time  = document.getElementById(prefix + 'action_time').value;
    const order_number = document.getElementById(prefix + 'order_number').value;
    const job_name     = document.getElementById(prefix + 'job_name').value;
    const item_type    = document.getElementById(prefix + 'type').value;
    const quantity     = document.getElementById(prefix + 'quantity').value;
    const weight       = document.getElementById(prefix + 'weight').value;
    const hours        = document.getElementById(prefix + 'hours').value;

    const formData = new FormData();
    formData.append('id', rowId);
    formData.append('action_type', action_type);
    formData.append('action_date', action_date);
    formData.append('action_time', action_time);
    formData.append('order_number', order_number);
    formData.append('job_name', job_name);
    formData.append('type', item_type);
    formData.append('quantity', quantity);
    formData.append('weight', weight);
    formData.append('hours', hours);

    fetch('save_db_entry.php', {
        method: 'POST',
        body: formData
    })
    .then(resp => resp.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Row updated successfully!');
        } else {
            alert('Error updating row: ' + data.message);
        }
    })
    .catch(err => {
        console.error('Save DB entry error:', err);
        alert('An error occurred while saving.');
    });
}

// Called by the pagination links e.g. "loadModifyDBEntries(nextPage)"
