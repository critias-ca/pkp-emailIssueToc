SELECT 
    tmp_submission_files.`path` AS list_file
FROM
    publications
JOIN 
    (SELECT T1.publication_id,
            T2.setting_value AS title_en,
            T3.setting_value AS title_fr
     FROM automatex
     JOIN publication_settings AS T1
       ON T1.setting_name = 'issueId' 
      AND T1.setting_value = automatex.variable_value
     LEFT JOIN publication_settings AS T2
       ON T2.publication_id = T1.publication_id 
      AND T2.setting_name = 'title'
      AND T2.locale = 'en_US'
     LEFT JOIN publication_settings AS T3
       ON T3.publication_id = T1.publication_id 
      AND T3.setting_name = 'title'
      AND T3.locale = 'fr_CA'
     WHERE automatex.variable_name = 'Issue_no') AS tmp_publication_settings
  ON publications.publication_id = tmp_publication_settings.publication_id
JOIN 
    (SELECT submission_files.submission_id,
            files.path 
     FROM submission_files
     JOIN files
       ON files.file_id = submission_files.file_id
     JOIN (SELECT submission_id, 
                  MAX(updated_at) AS max_updated_at
           FROM submission_files
           GROUP BY submission_id) AS tmp_max_modified
       ON submission_files.submission_id = tmp_max_modified.submission_id
      AND submission_files.updated_at = tmp_max_modified.max_updated_at) AS tmp_submission_files
  ON tmp_submission_files.submission_id = publications.submission_id
WHERE publications.section_id = 4
GROUP BY publications.submission_id
ORDER BY publications.section_id ASC,
         publications.seq ASC;
