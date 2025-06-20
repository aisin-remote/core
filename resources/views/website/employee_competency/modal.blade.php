<div class="modal fade" id="checksheetModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Competency Checksheets</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex justify-content-end mb-3 gap-4">
          <div><strong>Position:</strong> <span id="csPos">–</span></div>
        </div>
        <div class="table-responsive">
          <table class="table table-striped table-bordered align-middle table-hover" id="csTable">
            <thead class="table-dark">
              <tr>
                <th class="text-center" style="width:5%">No</th>
                <th class="text-center">Competency Name</th>
                <th class="text-center">Total Items</th>
                <th class="text-center" style="width:25%">Actions</th>
              </tr>
            </thead>
            <tbody>
              {{-- Data akan diisi Javascript --}}
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
  
  <script>
  document.addEventListener('DOMContentLoaded', () => {
    document.body.addEventListener('click', async e => {
      if (!e.target.closest('.checksheet-btn')) return;
      const btn = e.target.closest('.checksheet-btn');
      const empId = btn.dataset.employeeId;
      const pos = btn.dataset.position || '-';

      // Update header modal
      document.getElementById('csPos').textContent = pos;
  
      try {
        // Fetch data kompetensi beserta checksheet
        const res = await fetch(`/employeeCompetencies/${empId}/checksheet`, {
          headers: { 'Accept': 'application/json' }
        });
        const { competencies } = await res.json();
  
        const tbody = document.querySelector('#csTable tbody');
        tbody.innerHTML = '';
  
        if (!competencies?.length) {
          tbody.innerHTML = `
            <tr>
              <td colspan="4" class="text-center text-muted">
                No competencies found
              </td>
            </tr>`;
          return;
        }
  
        // Populasi data
        competencies.forEach((comp, index) => {
          const hasAssessment = comp.has_assessment;
          
          const row = `
            <tr>
              <td class="text-center">${index + 1}</td>
              <td class="text-center">${comp.name}</td>
              <td class="text-center">${comp.checksheets.length} Items</td>
              <td class="text-center">
                ${comp.checksheets.length > 0 ? `
                  ${hasAssessment ? `
                    <button class="btn btn-sm btn-primary" 
                      onclick="window.location.href='/checksheet-assessment/view/${comp.employee_competency_id}'">
                      View
                    </button>
                  ` : `
                    <button class="btn btn-sm btn-primary" 
                      onclick="startAssessment(${empId}, ${comp.id})">
                      View
                    </button>
                  `}
                ` : 'No Items'}
              </td>
            </tr>`;
          tbody.innerHTML += row;
        });
  
      } catch (error) {
        console.error('Failed to load checksheets:', error);
        tbody.innerHTML = `
          <tr>
            <td colspan="4" class="text-center text-danger">
              Error loading data
            </td>
          </tr>`;
      }
  
      // Tampilkan modal
      new bootstrap.Modal(document.getElementById('checksheetModal')).show();
    });
  });
  
  // Fungsi untuk memulai assessment
  function startAssessment(employeeId, competencyId) {
      const empId = parseInt(employeeId);
      const compId = parseInt(competencyId);
      
      if (isNaN(empId) || isNaN(compId)) {
          alert('ID tidak valid!');
          return;
      }
      
      window.location.href = `/checksheet-assessment/${empId}/${compId}`;
  }
  </script>