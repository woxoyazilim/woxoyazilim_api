-- Extra columns added after initial migration

-- Demo requests: software tracking
ALTER TABLE demo_requests ADD COLUMN IF NOT EXISTS software_name VARCHAR(255) DEFAULT NULL AFTER company;
ALTER TABLE demo_requests ADD COLUMN IF NOT EXISTS software_slug VARCHAR(255) DEFAULT NULL AFTER software_name;
ALTER TABLE demo_requests ADD COLUMN IF NOT EXISTS employee_count VARCHAR(50) DEFAULT NULL AFTER software_slug;

-- References: screenshot error
ALTER TABLE references_portfolio ADD COLUMN IF NOT EXISTS screenshot_error TEXT DEFAULT NULL AFTER screenshot_status;

-- References: subcategory
ALTER TABLE references_portfolio ADD COLUMN IF NOT EXISTS subcategory VARCHAR(100) DEFAULT NULL AFTER category;
