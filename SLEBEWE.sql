SELECT
   a.file_code,
   a.file_name,
   a.nama_dept,
   a.uploaded_at,
	u.username,
	f.id
FROM documents a
LEFT JOIN departements d ON a.nama_dept=d.`name`
LEFT JOIN users u ON a.created_by = u.username  -- or a.uploaded_by = u.id
LEFT JOIN folders f ON a.folder_id = f.id