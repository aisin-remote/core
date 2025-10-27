<div class="modal fade" id="apItemModal" tabindex="-1" aria-labelledby="apItemLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="apForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="apItemLabel">Tambah Activity Plan Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="apMode" value="create">
                    <input type="hidden" id="apRowId">

                    <div class="row g-3">

                        <!-- ===== IPP Point Info (read-only preview) ===== -->
                        <div class="col-12">
                            <div class="border rounded-3 p-3"
                                style="background:#f8fafc;border-color:#e5e7eb !important;">
                                <div class="d-flex flex-column flex-md-row flex-wrap gap-2">
                                    <div class="flex-grow-1">
                                        <div class="small text-muted mb-1">Activity / Program</div>
                                        <div class="fw-semibold text-dark" id="apPointActivity">—</div>
                                    </div>
                                    <div>
                                        <div class="small text-muted mb-1">Category</div>
                                        <div class="badge bg-light text-dark border" id="apPointCategory"
                                            style="border-color:#cbd5e1!important;">—</div>
                                    </div>
                                    <div>
                                        <div class="small text-muted mb-1">Periode Point</div>
                                        <div class="fw-semibold text-dark" id="apPointRange">—</div>
                                        <div class="text-muted small" id="apPointRangeNote" style="line-height:1.3;">
                                            Item kamu harus berada di rentang ini
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- IPP Point (hidden, tetap dikirim) -->
                        <div class="col-12 d-none">
                            <label class="form-label fs-6">IPP Point <span class="text-danger">*</span></label>
                            <select id="apPoint" class="form-select form-select-lg">
                                <option value="">— pilih IPP Point —</option>
                            </select>
                        </div>

                        <!-- Kind of Activity -->
                        <div class="col-md-12">
                            <label class="form-label fs-6">Kind of Activity <span class="text-danger">*</span></label>
                            <textarea class="form-control form-control-lg" id="apKind" rows="3"></textarea>
                        </div>

                        <!-- Target -->
                        <div class="col-md-12">
                            <label class="form-label fs-6">Target</label>
                            <textarea class="form-control form-control-lg" id="apTarget" rows="3"></textarea>
                        </div>

                        <!-- PIC -->
                        <div class="col-md-6">
                            <label class="form-label fs-6">PIC <span class="text-danger">*</span></label>
                            <select id="apPic" class="form-select form-select-lg">
                                <option value="">PILIH PIC</option>
                            </select>
                        </div>
                        <div class="col-md-6"></div>

                        <!-- Start & Due (per ITEM) -->
                        <div class="col-md-6">
                            <label class="form-label fs-6">Start Date Item <span class="text-danger">*</span></label>
                            <input type="date" id="apStart" class="form-control form-control-lg" />
                            <small class="text-muted">Harus dalam periode point.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-6">Due Date Item <span class="text-danger">*</span></label>
                            <input type="date" id="apDue" class="form-control form-control-lg" />
                            <small class="text-muted">Harus dalam periode point.</small>
                        </div>

                        <!-- Schedule (hidden, backend use only) -->
                        <div class="col-12 d-none" id="apScheduleWrapper">
                            <div class="d-flex flex-wrap gap-2">
                                @php $mList=['APR','MAY','JUN','JUL','AGT','SEPT','OCT','NOV','DEC','JAN','FEB','MAR']; @endphp
                                @foreach ($mList as $m)
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="m{{ $m }}"
                                            disabled>
                                        <label class="form-check-label"
                                            for="m{{ $m }}">{{ $m }}</label>
                                    </div>
                                @endforeach
                            </div>
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" id="apYearly" disabled>
                                <label class="form-check-label" for="apYearly">
                                    Diisi otomatis dari Start–Due item.
                                </label>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save2"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
