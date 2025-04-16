<!-- Wrap everything in a dedicated container to avoid parent layout conflicts -->
<div class="maintenance-container">
    <h2>Maintenance</h2>
    <div class="maintenance-buttons">
        <button onclick="loadActionMaintenance()">Fix Action Errors</button>
        <button onclick="loadProjectMaintenance()">Adjust Project Rates</button>
        <button onclick="loadDeleteProjects()">Delete Projects</button>
      	<button onclick="loadModifyDBEntries(1)">Modify Database Entries</button>
    </div>
    <div id="maintenanceContent"></div>
</div>