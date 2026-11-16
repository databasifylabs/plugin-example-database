SELECT json_object('connections', 0, 'queries', 0, 'dataMb', (SELECT page_count * page_size FROM pragma_page_count(), pragma_page_size()) / 1048576);
