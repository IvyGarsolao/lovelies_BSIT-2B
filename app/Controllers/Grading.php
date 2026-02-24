<?php

namespace App\Controllers;

use App\Models\GradeModel;
use App\Models\LogModel;
use CodeIgniter\Controller;

class Grade extends Controller
{
    public function index(){
        // Ensure you create a view folder named 'grade' with an 'index.php' file
        return view('grade/index');
    }

    public function save(){
        $model = new GradeModel();
        $logModel = new LogModel();

        // Adjust these POST keys to match your frontend form and database columns
        $data = [
            'student_id' => $this->request->getPost('student_id'),
            'subject_id' => $this->request->getPost('subject_id'),
            'grade'      => $this->request->getPost('grade'),
            'remarks'    => $this->request->getPost('remarks'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($model->insert($data)) {
            $logModel->addLog('Added new grade for Student ID: ' . $data['student_id'], 'ADD');
            return $this->response->setJSON(['status' => 'success']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to save grade']);
        }
    }

    public function edit($id){
        $model = new GradeModel();
        $grade = $model->find($id);

        if ($grade) {
            return $this->response->setJSON(['data' => $grade]);
        } else {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Grade record not found']);
        }
    }

    public function update(){
        $model = new GradeModel();
        $logModel = new LogModel();
        
        $id = $this->request->getPost('id');

        if (empty($id)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error: Grade ID is missing.']);
        }

        // Adjust these POST keys to match your frontend form and database columns
        $data = [
            'student_id' => $this->request->getPost('student_id'),
            'subject_id' => $this->request->getPost('subject_id'),
            'grade'      => $this->request->getPost('grade'),
            'remarks'    => $this->request->getPost('remarks'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $updated = $model->set($data)->where('id', $id)->update();

        if ($updated) {
            $logModel->addLog('Updated grade record ID: ' . $id, 'UPDATED');
            return $this->response->setJSON(['success' => true, 'message' => 'Grade updated successfully.']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Error updating grade. No changes were made.']);
        }
    }

    public function delete($id){
        $model = new GradeModel();
        $logModel = new LogModel();
        $grade = $model->find($id);

        if (!$grade) {
            return $this->response->setJSON(['success' => false, 'message' => 'Grade record not found.']);
        }

        if ($model->delete($id)) {
            $logModel->addLog('Deleted grade record ID: ' . $id, 'DELETED');
            return $this->response->setJSON(['success' => true, 'message' => 'Grade deleted successfully.']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete grade.']);
        }
    }

    public function fetchRecords()
    {
        $request = service('request');
        $model = new GradeModel();

        $start = $request->getPost('start') ?? 0;
        $length = $request->getPost('length') ?? 10;
        $searchValue = $request->getPost('search')['value'] ?? '';

        $totalRecords = $model->countAll();
        $result = $model->getRecords($start, $length, $searchValue);

        $data = [];
        $counter = $start + 1;
        foreach ($result['data'] as $row) {
            $row['row_number'] = $counter++;
            $data[] = $row;
        }

        return $this->response->setJSON([
            'draw' => intval($request->getPost('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $result['filtered'],
            'data' => $data,
        ]);
    }
}