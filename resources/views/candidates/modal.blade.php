
<div class="modal fade" id="candidateModal" tabindex="-1" aria-labelledby="candidateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="candidateModalLabel">Add/Edit Candidate</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="candidateForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" id="candidate_id">

                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone_number" id="phone_number" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Experience (Years)</label>
                        <input type="number" name="experience" id="experience" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Current Salary</label>
                        <input type="number" name="current_salary" id="current_salary" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Expected Salary</label>
                        <input type="number" name="expected_salary" id="expected_salary" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Offered Salary</label>
                        <input type="number" name="offered_salary" id="offered_salary" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Date of Joining</label>
                        <input type="date" name="date_of_joining" id="date_of_joining" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Comments</label>
                        <textarea name="comments" id="comments" class="form-control"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Resume</label>
                        <input type="file" name="resume" id="resume" class="form-control-file">
                    </div>

                    <div class="form-group">
                        <label>Department</label>
                        <select name="department_id" id="department_id" class="form-control" required>
                            <option value="">Select</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="Shortlist">Shortlist</option>
                            <option value="Scheduled">Scheduled</option>
                            <option value="Offered">Offered</option>
                            <option value="Hired">Hired</option>
                            <option value="Rejection Due to Salary Issue">Rejection Due to Salary Issue</option>
                            <option value="Hold">Hold</option>
                            <option value="Blacklisted">Blacklisted</option>
                            <option value="Technically Rejected">Technically Rejected</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" onclick="saveCandidate()">Save</button>
            </div>
        </div>
    </div>
</div>
