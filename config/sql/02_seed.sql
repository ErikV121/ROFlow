-- PostgreSQL seed data for ROFlow.
-- Run after config/sql/create_entities.sql.
--
-- Development login password for all seeded users:
--   password123

INSERT INTO users (email, password_hash, full_name, role) VALUES
    ('alex.rivera@servicelane.test',
     '$2y$10$pcVnzLR69U1pS6OS9gTuzO/ScoczCQxwZB0ul1jOyqkZqYvQ3XcLC',
     'Alex Rivera',
     'advisor'),
    ('marcus.webb@servicelane.test',
     '$2y$10$pcVnzLR69U1pS6OS9gTuzO/ScoczCQxwZB0ul1jOyqkZqYvQ3XcLC',
     'Marcus Webb',
     'technician'),
    ('jamie.chen@servicelane.test',
     '$2y$10$pcVnzLR69U1pS6OS9gTuzO/ScoczCQxwZB0ul1jOyqkZqYvQ3XcLC',
     'Jamie Chen',
     'technician');

INSERT INTO customers (full_name, phone, email) VALUES
    ('Sarah Johnson',   '423-555-0101', 'sarah.j@example.com'),
    ('Robert Mitchell', '423-555-0102', 'rmitchell@example.com'),
    ('Emily Carter',    '423-555-0103', 'emily.carter@example.com'),
    ('David Park',      '423-555-0104', NULL),
    ('Linda Foster',    '423-555-0105', 'lfoster@example.com');

INSERT INTO vehicles (customer_id, vin, year, make, model, color) VALUES
    (1, '4JGFB4JB1NA567890', 2022, 'Mercedes-Benz', 'GLE 350',   'Polar White'),
    (1, 'WDDZF4JB1KA123456', 2019, 'Mercedes-Benz', 'E 300',     'Selenite Gray'),
    (2, '5UXCR6C56KLL12345', 2019, 'BMW',           'X5',        'Black Sapphire'),
    (3, '5TDBKRFH7HS456789', 2017, 'Toyota',        'Highlander', 'Pearl White'),
    (4, '1HGCV1F37LA098765', 2020, 'Honda',         'Accord',    'Modern Steel'),
    (5, 'WAUFGAFL5DA234567', 2013, 'Audi',          'A4',        'Glacier White');

INSERT INTO repair_orders (
    ro_number, vehicle_id, advisor_id, technician_id,
    mileage, complaint, status
) VALUES (
    'RO-2026-0001', 1, 1, 2,
    47823, 'Customer reports grinding noise from front brakes when stopping.',
    'diagnosis'
);

INSERT INTO repair_orders (
    ro_number, vehicle_id, advisor_id, technician_id,
    mileage, complaint, status,
    customer_token, token_expires_at, inspection_submitted_at
) VALUES (
    'RO-2026-0002', 3, 1, 2,
    62104, 'Annual service. Customer also mentions check engine light came on yesterday.',
    'awaiting_approval',
    'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4',
    CURRENT_TIMESTAMP + INTERVAL '7 days',
    CURRENT_TIMESTAMP - INTERVAL '2 hours'
);

INSERT INTO repair_orders (
    ro_number, vehicle_id, advisor_id, technician_id,
    mileage, complaint, status,
    customer_token, token_expires_at, inspection_submitted_at
) VALUES (
    'RO-2026-0003', 4, 1, 3,
    98452, 'Oil change and tire rotation. Tire pressure light has been on intermittently.',
    'repair',
    'z9y8x7w6v5u4t3s2r1q0p9o8n7m6l5k4j3i2h1g0f9e8d7c6',
    CURRENT_TIMESTAMP + INTERVAL '7 days',
    CURRENT_TIMESTAMP - INTERVAL '1 day'
);

INSERT INTO repair_orders (
    ro_number, vehicle_id, advisor_id, technician_id,
    mileage, complaint, status,
    inspection_submitted_at
) VALUES (
    'RO-2026-0004', 5, 1, 3,
    34521, 'Routine 30k mile service.',
    'ready_for_pickup',
    CURRENT_TIMESTAMP - INTERVAL '3 days'
);

INSERT INTO repair_orders (
    ro_number, vehicle_id, advisor_id, technician_id,
    mileage, complaint, status,
    inspection_submitted_at, closed_at
) VALUES (
    'RO-2026-0005', 6, 1, 2,
    142390, 'Squealing belt noise on cold starts.',
    'closed',
    CURRENT_TIMESTAMP - INTERVAL '14 days',
    CURRENT_TIMESTAMP - INTERVAL '12 days'
);

INSERT INTO inspection_findings (
    repair_order_id, title, description, estimated_cost, approval_status
) VALUES
    (2, 'Front Brake Pads',
     '2mm of pad remaining - at minimum spec, replacement strongly recommended.',
     385.00, 'pending'),
    (2, 'Engine Air Filter',
     'Visibly dirty, restricting airflow. Will affect MPG soon.',
     45.00, 'pending'),
    (2, 'Cabin Air Filter',
     'Dust accumulation, recommend replacement at next service.',
     35.00, 'pending'),
    (2, 'Rear Tire Tread',
     'Tread at 4/32 inch - within wear bar window, replace within 6 months.',
     780.00, 'pending');

INSERT INTO inspection_findings (
    repair_order_id, title, description, estimated_cost,
    approval_status, customer_decided_at
) VALUES
    (3, 'Front Tire Rotation',
     'Tires showing uneven wear pattern. Rotation recommended.',
     45.00, 'approved', CURRENT_TIMESTAMP - INTERVAL '18 hours'),
    (3, 'Wiper Blades (Both)',
     'Streaking on windshield, blades cracked.',
     38.00, 'approved', CURRENT_TIMESTAMP - INTERVAL '18 hours'),
    (3, 'Coolant Flush',
     'Coolant slightly contaminated, recommend flush per maintenance schedule.',
     145.00, 'declined', CURRENT_TIMESTAMP - INTERVAL '18 hours');

INSERT INTO inspection_findings (
    repair_order_id, title, description, estimated_cost,
    approval_status, customer_decided_at
) VALUES
    (5, 'Serpentine Belt',
     'Visible cracking and glazing - failure imminent.',
     220.00, 'approved', CURRENT_TIMESTAMP - INTERVAL '13 days');

-- Verification:
-- SELECT COUNT(*) FROM users;
-- SELECT COUNT(*) FROM customers;
-- SELECT COUNT(*) FROM vehicles;
-- SELECT COUNT(*) FROM repair_orders;
-- SELECT COUNT(*) FROM inspection_findings;
