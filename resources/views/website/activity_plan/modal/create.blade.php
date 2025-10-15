<div class="modal fade" id="apItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="apItemLabel" class="modal-title fw-bold">Tambah Activity Plan Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form id="apForm">
                <div class="modal-body">
                    <input type="hidden" id="apMode" value="create">
                    <input type="hidden" id="apRowId" value="">

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label req" for="apPoint">IPP Point (Category/Activity/Start→Due)</label>
                            <select id="apPoint" class="form-select" required></select>
                            <div class="form-text">Data category, activity, start & due akan ditarik otomatis dari IPP
                                Point.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label req" for="apKind">Kind of Activity</label>
                            <input type="text" id="apKind" class="form-control"
                                placeholder="Feasibility Study / Development / Testing" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label req" for="apPic">PIC</label>
                            <select id="apPic" class="form-select" required></select>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="apTarget">Target</label>
                            <textarea id="apTarget" class="form-control" rows="2" placeholder="Target / deliverable"></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Schedule (FY Apr–Mar)</label>
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <div class="form-check form-switch me-3">
                                    <input class="form-check-input" type="checkbox" id="apYearly">
                                    <label class="form-check-label" for="apYearly">Yearly (centang semua)</label>
                                </div>
                                @php $months = ['APR','MAY','JUN','JUL','AGT','SEPT','OCT','NOV','DEC','JAN','FEB','MAR']; @endphp
                                @foreach ($months as $m)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="m{{ $m }}">
                                        <label class="form-check-label"
                                            for="m{{ $m }}">{{ $m }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="apSaveBtn">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
