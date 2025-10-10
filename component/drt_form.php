<div class="modal" id="modal-dtr" tabindex="-1" role="dialog">
    <form class="form-auth-small" id="fileUploadForm" enctype="multipart/form-data" method="post" novalidate>
        <input type="hidden" name="id" id="id">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="defaultModalLabel">Upload File</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row clearfix">
                        <div class="col-md-12">
                            <div class="form-group" >
                                <label>Select File (fileBiometric.txt)</label>
                                <input type="file"  placeholder="fileBiometric.txt" name="fileBiometric" id="fileBiometric" class="form-control" required data-parsley-required-message="File  is required.">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group" >
                                <label>Select File (fileDB.txt)</label>
                                <input type="file"  placeholder="fileDB.txt" name="fileDB" id="fileDB" class="form-control" required data-parsley-required-message="File  is required.">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info submitbutton"> Upload</button>
                </div>
            </div>
        </div>
    </form>
</div>