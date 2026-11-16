SELECT json_object('users', (SELECT json_group_array(name) FROM users), 'databases', (SELECT json_group_array(name) FROM databases));
