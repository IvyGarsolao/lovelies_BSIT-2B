<?= $this->extend('theme/template') ?>

<?= $this->section('content') ?>
<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">Grades</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
            <li class="breadcrumb-item active">Grades</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">List of Grades</h3>
              <div class="float-right">
                <button type="button" class="btn btn-md btn-primary" data-toggle="modal" data-target="#AddGradeModal">
                  <i class="fa fa-plus-circle fa fw"></i> Add New
                </button>
              </div>
            </div>
            <div class="card-body">
               <table id="gradeTable" class="table table-bordered table-striped table-sm">
                <thead>
                  <tr>
                    <th>No.</th>
                    <th style="display:none;">id</th>
                    <th>Student ID</th>
                    <th>Subject ID</th>
                    <th>Grade</th>
                    <th>Remarks</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="AddGradeModal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <form id="addGradeForm">
          <?= csrf_field() ?>
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title"><i class="fa fa-plus-circle fa fw"></i> Add New Grade</h5>
              <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
              <div class="form-group">
                <label>Student ID</label>
                <input type="number" name="student_id" class="form-control" required />
              </div>
              <div class="form-group">
                <label>Subject ID</label>
                <input type="number" name="subject_id" class="form-control" required />
              </div>
              <div class="form-group">
                <label>Grade</label>
                <input type="text" name="grade" class="form-control" required />
              </div>
              <div class="form-group">
                <label>Remarks</label>
                <textarea name="remarks" class="form-control" rows="3"></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class='fas fa-times-circle'></i> Cancel</button>
              <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <div class="modal fade" id="editGradeModal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><i class="far fa-edit fa fw"></i> Edit Grade</h5>
            <button type="button" class="close" data-dismiss="modal">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form id="editGradeForm">
             <?= csrf_field() ?>
            <div class="modal-body">
              <input type="hidden" id="edit_id" name="id">
              <div class="form-group">
                  <label>Student ID</label>
                  <input type="number" name="student_id" id="edit_student_id" class="form-control" required />
              </div>
              <div class="form-group">
                <label>Subject ID</label>
                <input type="number" class="form-control" id="edit_subject_id" name="subject_id" required>
              </div>
              <div class="form-group">
                <label>Grade</label>
                <input type="text" class="form-control" id="edit_grade" name="grade" required>
              </div>
              <div class="form-group">
                <label>Remarks</label>
                <textarea class="form-control" id="edit_remarks" name="remarks" rows="3"></textarea>
              </div>        
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class='fas fa-times-circle'></i> Cancel</button>
              <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>
</div>
<div class="toasts-top-right fixed" style="position: fixed; top: 1rem; right: 1rem; z-index: 9999;"></div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function () {
    const baseUrl = "<?= base_url() ?>";

    let table = $('#gradeTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: baseUrl + 'grades/fetchRecords',
            type: 'POST',
            data: function (d) {
                d.<?= csrf_token() ?> = '<?= csrf_hash() ?>'; 
            }
        },
        columns: [
            { data: 'row_number', orderable: false },
            { data: 'id', visible: false },
            { data: 'student_id' },
            { data: 'subject_id' },
            { data: 'grade' },
            { data: 'remarks' },
            {
                data: null,
                render: function (data, type, row) {
                    return `
                        <button class="btn btn-sm btn-warning edit-btn" data-id="${row.id}">
                            <i class="fas fa-edit" style="color:white;"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="${row.id}">
                            <i class="fas fa-trash"></i>
                        </button>`;
                },
                orderable: false
            }
        ]
    });

    // ==========================================
    // OFFLINE & SYNC LOGIC FOR ADDING GRADES
    // ==========================================
    
    function saveGradeOffline(formData) {
        let offlineGrades = JSON.parse(localStorage.getItem('offlineGrades')) || [];
        offlineGrades.push(formData);
        localStorage.setItem('offlineGrades', JSON.stringify(offlineGrades));
        
        toastr.info('You are offline. Grade saved locally and will sync when online.');
        
        $('#AddGradeModal').modal('hide');
        $('#addGradeForm')[0].reset();
        table.draw(false); 
    }

    function syncOfflineGrades() {
        let offlineGrades = JSON.parse(localStorage.getItem('offlineGrades')) || [];
        
        if (offlineGrades.length > 0) {
            toastr.info('Network restored. Syncing ' + offlineGrades.length + ' offline grade(s)...');
            
            let remainingGrades = [];

            offlineGrades.forEach(function(formData) {
                $.ajax({
                    url: baseUrl + 'grades/save',
                    type: 'POST',
                    data: formData,
                    success: function (response) {
                        if (response.status === 'success') {
                            toastr.success('Offline grade synced successfully!');
                            table.ajax.reload(null, false);
                        } else {
                            toastr.error('Failed to sync a grade: ' + response.message);
                        }
                    },
                    error: function() {
                        remainingGrades.push(formData);
                    }
                });
            });

            localStorage.setItem('offlineGrades', JSON.stringify(remainingGrades));
        }
    }

    window.addEventListener('online', syncOfflineGrades);

    if (navigator.onLine) {
        syncOfflineGrades();
    }

    // Add Grade Submit Event
    $('#addGradeForm').submit(function (e) {
        e.preventDefault();
        
        let formData = $(this).serialize();

        // **Invert the logic for the offline check to match your setup**
        if (!navigator.onLine) { 
            saveGradeOffline(formData);
        } else {
            $.ajax({
                url: baseUrl + 'grades/save',
                type: 'POST',
                data: formData,
                success: function (response) {
                    if (response.status === 'success') {
                        $('#AddGradeModal').modal('hide');
                        $('#addGradeForm')[0].reset();
                        table.ajax.reload();
                        toastr.success('Grade added successfully!');
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    saveGradeOffline(formData);
                }
            });
        }
    });

    // ==========================================
    // EXISTING EDIT & DELETE LOGIC
    // ==========================================

    // Fetch Data for Edit
    $('#gradeTable').on('click', '.edit-btn', function () {
        let id = $(this).data('id');
        $.get(baseUrl + 'grades/edit/' + id, function (response) {
            $('#edit_id').val(response.data.id);
            $('#edit_student_id').val(response.data.student_id);
            $('#edit_subject_id').val(response.data.subject_id);
            $('#edit_grade').val(response.data.grade);
            $('#edit_remarks').val(response.data.remarks);
            $('#editGradeModal').modal('show');
        });
    });

    // Update Grade
    $('#editGradeForm').submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: baseUrl + 'grades/update',
            type: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                if (response.success) {
                    $('#editGradeModal').modal('hide');
                    table.ajax.reload();
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            }
        });
    });

    // Delete Grade
    $('#gradeTable').on('click', '.delete-btn', function () {
        let id = $(this).data('id');
        if (confirm('Are you sure you want to delete this grade record?')) {
            $.ajax({
                url: baseUrl + 'grades/delete/' + id,
                type: 'DELETE',
                data: {
                    <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                },
                success: function (response) {
                    if (response.success) {
                        table.ajax.reload();
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                }
            });
        }
    });
});
</script>
<?= $this->endSection() ?>