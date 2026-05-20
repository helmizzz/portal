<?php

// Fungsi untuk memeriksa apakah user memiliki hak akses ke departemen tertentu
function userHasAccessToDepartement($conn, $user_id, $departement_id) {
    if ($departement_id === null) {
        return true;
    }

    $sql = "SELECT id FROM users WHERE id = ? AND departement_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare statement failed: " . $conn->error);
        return false;
    }
    $stmt->bind_param("ii", $user_id, $departement_id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows > 0;
}

// Fungsi untuk mencatat aktivitas ke tabel activity_logs
function logActivity($conn, $user_id, $activity_type, $description, $document_id = null, $folder_id = null) {
    $sql = "INSERT INTO activity_logs (user_id, activity_type, description, document_id, folder_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("issii", $user_id, $activity_type, $description, $document_id, $folder_id);
        if (!$stmt->execute()) {
             error_log("Failed to execute log activity: " . $stmt->error);
        }
        $stmt->close();
    } else {
        error_log("Failed to prepare statement for logging activity: " . $conn->error);
    }
}

// Fungsi untuk mengambil izin (permissions) user
function getUserPermissions($conn, $user_id) {
    $permissions = [];
    $sql = "SELECT p.permission_name 
            FROM permissions p
            JOIN role_permissions rp ON p.id = rp.permission_id
            JOIN users u ON rp.role_id = u.role_id
            WHERE u.id = ?";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $permissions[] = $row['permission_name'];
        }
        $stmt->close();
    }
    return $permissions;
}

/**
 * FUNGSI BARU
 * Membuat notifikasi untuk pengguna.
 */
function create_notification($conn, $target_user_ids, $message, $link = null, $exclude_user_id = null) {
    $sql_insert = "INSERT INTO notifications (user_id, message, link) VALUES (?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    
    if ($target_user_ids === 'all') {
        $sql_users = "SELECT id FROM users";
        $result_users = $conn->query($sql_users);
        while ($user = $result_users->fetch_assoc()) {
            if ($user['id'] != $exclude_user_id) {
                $stmt_insert->bind_param("iss", $user['id'], $message, $link);
                $stmt_insert->execute();
            }
        }
    } elseif (is_array($target_user_ids)) {
        foreach ($target_user_ids as $user_id) {
             if ($user_id != $exclude_user_id) {
                $stmt_insert->bind_param("iss", $user_id, $message, $link);
                $stmt_insert->execute();
            }
        }
    } else { // Single user ID
        if ($target_user_ids != $exclude_user_id) {
            $stmt_insert->bind_param("iss", $target_user_ids, $message, $link);
            $stmt_insert->execute();
        }
    }
    
    $stmt_insert->close();
}

/**
 * Memperbarui aturan akses (departemen & user) untuk Folder atau Dokumen.
 * // ta 
*/
function update_access_rules($conn, $id, $type, $departements_access = [], $users_access = []) {
    $dept_table = ($type === 'folder') ? 'folder_departments' : 'document_departments';
    $user_table = ($type === 'folder') ? 'folder_user_access' : 'document_user_access';
    $id_column = ($type === 'folder') ? 'folder_id' : 'document_id';

    // 1. Hapus aturan lama
    $conn->query("DELETE FROM $dept_table WHERE $id_column = $id");
    $conn->query("DELETE FROM $user_table WHERE $id_column = $id");

    // 2. Insert aturan departemen
    if (!empty($departements_access)) {
        $stmt_dept = $conn->prepare("INSERT INTO $dept_table ($id_column, departement_id) VALUES (?, ?)");
        foreach ($departements_access as $dept_id) {
            $stmt_dept->bind_param("ii", $id, $dept_id);
            $stmt_dept->execute();
        }
        $stmt_dept->close();
    }

    // 3. Insert aturan user khusus
    if (!empty($users_access)) {
        $stmt_user = $conn->prepare("INSERT INTO $user_table ($id_column, user_id) VALUES (?, ?)");
        foreach ($users_access as $user_id) {
            $stmt_user->bind_param("ii", $id, $user_id);
            $stmt_user->execute();
        }
        $stmt_user->close();
    }
}
// tambahan 17/01
