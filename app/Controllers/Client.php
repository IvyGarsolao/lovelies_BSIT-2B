<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;
use App\Models\LogModel;
use App\Models\ClientModel;

class Client extends Controller
{
    public function index(){
        $model = new ClientModel();
        $data['client'] = $model->findAll();
        return view('client/index', $data);
    }

    public function save(){
        $name = $this->request->getPost('name');
        $email = $this->request->getPost('email');
        $address = $this->request->getPost('address');

        $userModel = new \App\Models\ClientModel();
        $logModel = new LogModel();

        $data = [
            'name'       => $name,
            'email'       => $email,
            'address'    => $address
        ];

        if ($userModel->insert($data)) {
            $logModel->addLog('New client has been added: ' . $name, 'ADD');
            return $this->response->setJSON(['status' => 'success']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to save client']);
        }
    }

    public function update(){
        $model = new ClientModel();
        $logModel = new LogModel();
        $userId = $this->request->getPost('id');
        $name = $this->request->getPost('name');
        $email = $this->request->getPost('email');
        $address = $this->request->getPost('address');

        $userData = [
            'name'       => $name,
            'email'       => $email,
            'address'    => $address
        ];

        $updated = $model->update($userId, $userData);

        if ($updated) {
            $logModel->addLog('New User has been apdated: ' . $name, 'UPDATED');
            return $this->response->setJSON([
                'success' => true,
                'message' => 'User updated successfully.'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error updating user.'
            ]);
        }
    }

    public function edit($id){
    $model = new ClientModel();
    $user = $model->find($id); // Fetch user by ID

    if ($user) {
        return $this->response->setJSON(['data' => $user]); // Return user data as JSON
    } else {
        return $this->response->setStatusCode(404)->setJSON(['error' => 'User not found']);
    }
}

public function delete($id){
    $model = new ClientModel();
    $logModel = new LogModel();
    $user = $model->find($id);
    if (!$user) {
        return $this->response->setJSON(['success' => false, 'message' => 'User not found.']);
    }

    $deleted = $model->delete($id);

    if ($deleted) {
        $logModel->addLog('Delete user', 'DELETED');
        return $this->response->setJSON(['success' => true, 'message' => 'User deleted successfully.']);
    } else {
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete user.']);
    }
}

public function fetchRecords()
{
    $request = service('request');
    $model = new \App\Models\ClientModel();

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
