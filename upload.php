<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Kiểm tra dung lượng gửi lên
    if (empty($_FILES) && empty($_POST) && isset($_SERVER['CONTENT_LENGTH'])) {
        ob_end_clean();
        echo json_encode([
            'status'  => 'error',
            'message' => 'Tổng dung lượng vượt quá giới hạn cấu hình Server!'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (isset($_FILES['files']) && is_array($_FILES['files']['name'])) {$uploadFileDir = './uploads/';
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }

        $uploadedCount = 0;
        $totalFiles = count($_FILES['files']['name']);$uploadedFilesData = []; // Mảng chứa thông tin các file lưu thành công
        $errors = [];

        for ($i = 0; $i < $totalFiles; $i++) {
            if ($_FILES['files']['error'][$i] === UPLOAD_ERR_OK) {$fileTmpPath = $_FILES['files']['tmp_name'][$i];
                $fileName =$_FILES['files']['name'][$i];$fileType = $_FILES['files']['type'][$i];
                $fileSize =$_FILES['files']['size'][$i];$newFileName = time() . '_' . $i . '_' .$fileName;
                $destPath = $uploadFileDir .$newFileName;

                if (move_uploaded_file($fileTmpPath, $destPath)) {$uploadedCount++;
                    // Thêm thông tin file vào mảng phản hồi
                    $uploadedFilesData[] = [
                        'name' => $fileName,
                        'path' => $destPath, // Đường dẫn file trên server
                        'size' => round($fileSize / (1024 * 1024), 2) . ' MB',
                        'type' => $fileType
                    ];
                } else {
                    $errors[] = "Không thể lưu tệp: " . $fileName;
                }
            } else {
                $errors[] = "Lỗi tệp " . $_FILES['files']['name'][$i];
            }
        }

        ob_end_clean();
        if ($uploadedCount > 0) {
            echo json_encode([
                'status'        => 'success',
                'message'       => "Đã tải lên thành công $uploadedCount/$totalFiles tệp!",
                'uploadedFiles' => $uploadedFilesData // Trả danh sách file về JS
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Tải lên thất bại: ' . implode(', ', $errors)
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;

    } else {
        ob_end_clean();
        echo json_encode([
            'status'  => 'error',
            'message' => 'Chưa chọn tệp hợp lệ!'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
?>