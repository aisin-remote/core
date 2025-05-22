<div class="modal fade" id="checksheetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Competency Checksheets</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="d-flex justify-content-end mb-3 gap-4">
            <div><strong>NPK:</strong> <span id="csNpk">–</span></div>
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
                <!-- Data akan diisi oleh JavaScript -->
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
    // Handler untuk tombol checksheet
    document.body.addEventListener('click', async e => {
      if (!e.target.closest('.checksheet-btn')) return;
      const btn = e.target.closest('.checksheet-btn');
      const empId = btn.dataset.employeeId;
      const npk = btn.dataset.npk || '-';
      const pos = btn.dataset.position || '-';
  
      // Update header modal
      document.getElementById('csNpk').textContent = npk;
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
          const row = `
            <tr>
              <td class="text-center">${index + 1}</td>
              <td class="text-center">${comp.name}</td>
              <td class="text-center">${comp.checksheets.length} Items</td>
              <td class="text-center">
                <button class="btn btn-sm btn-primary" 
                  onclick="window.location.href='/checksheet-assessment/${comp.id}'"
                  ${comp.checksheets.length === 0 ? 'disabled' : ''}>
                  ${comp.checksheets.length > 0 ? 'View' : 'No Items'}
                </button>
                ${comp.checksheets.length > 0 ? `
                  <button class="btn btn-sm btn-success" 
                    onclick="startAssessment(${comp.id})">
                    Start
                  </button>` : ''
                }
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
  
    // Handler untuk menghapus checksheet
    document.body.addEventListener('click', async e => {
      if (!e.target.classList.contains('delete-cs')) return;
      const id = e.target.dataset.id;
      if (!confirm('Delete this checksheet?')) return;
      
      try {
        const r = await fetch(`/checksheet/${id}`, {
          method: 'DELETE',
          headers: { 
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          }
        });
        const j = await r.json();
        if (j.success) location.reload();
        else alert('Delete failed: ' + (j.message || 'Unknown error'));
      } catch (error) {
        console.error('Delete error:', error);
        alert('Delete failed');
      }
    });
  });
  
  // Fungsi untuk memulai assessment
  function startAssessment(competencyId) {
    window.location.href = `/checksheet-assessment/${competencyId}?action=new`;
  }
  </script>