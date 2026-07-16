-- WOXOYAZILIM MySQL Migration
-- Supabase KV Store → Normalized MySQL Tables
-- Run: mysql -u root woxoyazilim < scripts/migrate.sql

-- ==========================================
-- AUTH
-- ==========================================
CREATE TABLE IF NOT EXISTS users (
  id CHAR(36) PRIMARY KEY,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  name VARCHAR(255),
  role ENUM('admin','user') DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ==========================================
-- SERVICES (Hizmetler)
-- ==========================================
CREATE TABLE IF NOT EXISTS services (
  id VARCHAR(100) PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(255),
  short_description TEXT,
  description TEXT,
  content JSON,
  icon VARCHAR(100),
  image VARCHAR(500),
  category VARCHAR(100),
  technologies JSON,
  features JSON,
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  seo_title VARCHAR(255),
  seo_description TEXT,
  seo_keywords TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_slug (slug),
  INDEX idx_category (category)
);

-- ==========================================
-- MESSAGES (Iletisim Mesajlari)
-- ==========================================
CREATE TABLE IF NOT EXISTS messages (
  id VARCHAR(100) PRIMARY KEY,
  name VARCHAR(255),
  email VARCHAR(255),
  phone VARCHAR(50),
  subject VARCHAR(255),
  message TEXT,
  status ENUM('new','read','replied','closed') DEFAULT 'new',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_status (status)
);

-- ==========================================
-- DEMO REQUESTS (Demo Talepleri)
-- ==========================================
CREATE TABLE IF NOT EXISTS demo_requests (
  id VARCHAR(100) PRIMARY KEY,
  name VARCHAR(255),
  email VARCHAR(255),
  phone VARCHAR(50),
  company VARCHAR(255),
  service_id VARCHAR(100),
  message TEXT,
  status ENUM('pending','contacted','completed','cancelled') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_status (status)
);

-- ==========================================
-- CHAT LEADS
-- ==========================================
CREATE TABLE IF NOT EXISTS chat_leads (
  id VARCHAR(100) PRIMARY KEY,
  name VARCHAR(255),
  email VARCHAR(255),
  phone VARCHAR(50),
  messages JSON,
  source VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ==========================================
-- PAGES (CMS Sayfalari)
-- ==========================================
CREATE TABLE IF NOT EXISTS pages (
  id VARCHAR(100) PRIMARY KEY,
  slug VARCHAR(255) UNIQUE,
  title VARCHAR(255),
  content JSON,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_slug (slug)
);

-- ==========================================
-- REFERENCES (Referanslar / Portfolio)
-- ==========================================
CREATE TABLE IF NOT EXISTS references_portfolio (
  id VARCHAR(100) PRIMARY KEY,
  title VARCHAR(255),
  description TEXT,
  image VARCHAR(500),
  screenshot_url VARCHAR(500) DEFAULT NULL,
  screenshot_status ENUM('pending','success','failed') DEFAULT NULL,
  screenshot_updated_at TIMESTAMP NULL DEFAULT NULL,
  url VARCHAR(500),
  category VARCHAR(100),
  services JSON,
  year VARCHAR(10),
  industry VARCHAR(100),
  type VARCHAR(50) DEFAULT 'web',
  featured TINYINT(1) DEFAULT 0,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_type (type),
  INDEX idx_featured (featured)
);

-- ==========================================
-- ERP: CUSTOMERS (Cariler)
-- ==========================================
CREATE TABLE IF NOT EXISTS erp_customers (
  id VARCHAR(100) PRIMARY KEY,
  customer_number VARCHAR(50) UNIQUE,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255),
  phone VARCHAR(50),
  address TEXT,
  city VARCHAR(100),
  tax_office VARCHAR(100),
  tax_number VARCHAR(50),
  type ENUM('individual','company') DEFAULT 'company',
  balance DECIMAL(15,2) DEFAULT 0,
  notes TEXT,
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_status (status)
);

-- ==========================================
-- ERP: PRODUCTS (Urunler)
-- ==========================================
CREATE TABLE IF NOT EXISTS erp_products (
  id VARCHAR(100) PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  category VARCHAR(100),
  unit VARCHAR(50) DEFAULT 'adet',
  price DECIMAL(15,2) DEFAULT 0,
  currency VARCHAR(10) DEFAULT 'TRY',
  tax_rate DECIMAL(5,2) DEFAULT 20,
  stock_quantity INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ==========================================
-- ERP: PROJECTS (Projeler)
-- ==========================================
CREATE TABLE IF NOT EXISTS erp_projects (
  id VARCHAR(100) PRIMARY KEY,
  project_number VARCHAR(50) UNIQUE,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  customer_id VARCHAR(100),
  start_date DATE,
  end_date DATE,
  budget DECIMAL(15,2),
  currency VARCHAR(10) DEFAULT 'TRY',
  status ENUM('planning','active','completed','on-hold','cancelled') DEFAULT 'planning',
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_status (status),
  INDEX idx_customer (customer_id)
);

-- ==========================================
-- ERP: SALES (Satislar)
-- ==========================================
CREATE TABLE IF NOT EXISTS erp_sales (
  id VARCHAR(100) PRIMARY KEY,
  sale_number VARCHAR(50),
  customer_id VARCHAR(100),
  items JSON,
  subtotal DECIMAL(15,2) DEFAULT 0,
  tax_total DECIMAL(15,2) DEFAULT 0,
  total DECIMAL(15,2) DEFAULT 0,
  currency VARCHAR(10) DEFAULT 'TRY',
  status ENUM('draft','confirmed','invoiced','cancelled') DEFAULT 'draft',
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_customer (customer_id),
  INDEX idx_status (status)
);

-- ==========================================
-- ERP: TASKS (Gorevler)
-- ==========================================
CREATE TABLE IF NOT EXISTS erp_tasks (
  id VARCHAR(100) PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  project_id VARCHAR(100),
  project_name VARCHAR(255),
  assigned_to VARCHAR(255),
  priority ENUM('low','medium','high','urgent') DEFAULT 'medium',
  status ENUM('todo','in_progress','review','done','cancelled') DEFAULT 'todo',
  due_date DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_project (project_id),
  INDEX idx_status (status)
);

-- ==========================================
-- ERP: QUOTATIONS (Teklifler)
-- ==========================================
CREATE TABLE IF NOT EXISTS erp_quotations (
  id VARCHAR(100) PRIMARY KEY,
  quotation_number VARCHAR(50) UNIQUE,
  customer_id VARCHAR(100),
  customer_name VARCHAR(255),
  items JSON,
  subtotal DECIMAL(15,2) DEFAULT 0,
  tax_total DECIMAL(15,2) DEFAULT 0,
  total DECIMAL(15,2) DEFAULT 0,
  currency VARCHAR(10) DEFAULT 'TRY',
  valid_until DATE,
  status ENUM('draft','sent','approved','rejected','expired') DEFAULT 'draft',
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_customer (customer_id),
  INDEX idx_status (status)
);

-- ==========================================
-- ERP: TRANSACTIONS (Islemler / Finansal Hareketler)
-- ==========================================
CREATE TABLE IF NOT EXISTS erp_transactions (
  id VARCHAR(100) PRIMARY KEY,
  type ENUM('income','expense') NOT NULL,
  category VARCHAR(100),
  customer_id VARCHAR(100),
  customer_name VARCHAR(255),
  amount DECIMAL(15,2) NOT NULL,
  currency VARCHAR(10) DEFAULT 'TRY',
  description TEXT,
  reference_no VARCHAR(100),
  transaction_date DATE,
  status ENUM('pending','completed','cancelled') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_type (type),
  INDEX idx_customer (customer_id)
);

-- ==========================================
-- ERP: DOMAINS (Alanadi / Hosting)
-- ==========================================
CREATE TABLE IF NOT EXISTS erp_domains (
  id VARCHAR(100) PRIMARY KEY,
  domain_name VARCHAR(255) NOT NULL,
  registrar VARCHAR(100),
  customer_id VARCHAR(100),
  customer_name VARCHAR(255),
  registration_date DATE,
  expiry_date DATE,
  auto_renew TINYINT(1) DEFAULT 1,
  hosting_provider VARCHAR(100),
  hosting_plan VARCHAR(100),
  hosting_expiry DATE,
  dns_provider VARCHAR(100),
  ssl_status ENUM('active','expired','none') DEFAULT 'none',
  status ENUM('active','inactive','expired','transferred') DEFAULT 'active',
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_customer (customer_id),
  INDEX idx_status (status)
);

-- ==========================================
-- ERP: TEAM (Ekip)
-- ==========================================
CREATE TABLE IF NOT EXISTS erp_team (
  id VARCHAR(100) PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  role VARCHAR(255),
  bio TEXT,
  email VARCHAR(255),
  phone VARCHAR(50),
  image VARCHAR(500),
  social_links JSON,
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ==========================================
-- REFERENCE CATEGORIES
-- ==========================================
CREATE TABLE IF NOT EXISTS reference_categories (
  id VARCHAR(100) PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  slug VARCHAR(255),
  icon VARCHAR(50) DEFAULT 'Folder',
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_slug (slug)
);

-- ==========================================
-- LANDING PAGES (SEO / Bolgesel Sayfalar)
-- ==========================================
CREATE TABLE IF NOT EXISTS landing_pages (
  id VARCHAR(100) PRIMARY KEY,
  region VARCHAR(100),
  service_title VARCHAR(255),
  slug VARCHAR(255) UNIQUE,
  h1 VARCHAR(255),
  meta_title VARCHAR(255),
  meta_description TEXT,
  content JSON,
  hero_subtitle TEXT,
  cta_text VARCHAR(255),
  cta_url VARCHAR(500),
  faq JSON,
  is_active TINYINT(1) DEFAULT 1,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_slug (slug),
  INDEX idx_region (region)
);

-- ==========================================
-- PAGE CONTENT (Sayfa Icerikleri / CMS)
-- ==========================================
CREATE TABLE IF NOT EXISTS page_content (
  slug VARCHAR(255) PRIMARY KEY,
  content JSON,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ==========================================
-- SCREENSHOT COLUMNS (Referanslar icin)
-- ==========================================
-- ALTER TABLE references_portfolio
--   ADD COLUMN screenshot_url VARCHAR(500) DEFAULT NULL AFTER image,
--   ADD COLUMN screenshot_status ENUM('pending','success','failed') DEFAULT NULL AFTER screenshot_url,
--   ADD COLUMN screenshot_updated_at TIMESTAMP NULL DEFAULT NULL AFTER screenshot_status;
-- (Bu kolonlar yeni kurulumda asagidaki CREATE TABLE'a dahil edilmistir)

-- ==========================================
-- SECTORS (Sektorler)
-- ==========================================
CREATE TABLE IF NOT EXISTS sectors (
  id VARCHAR(100) PRIMARY KEY,
  name VARCHAR(255) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- SUBCATEGORIES (Alt Kategoriler)
-- ==========================================
CREATE TABLE IF NOT EXISTS subcategories (
  id VARCHAR(100) PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  sector_id VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sector (sector_id)
);

-- ==========================================
-- SERVICE TAGS (Hizmet Etiketleri)
-- ==========================================
CREATE TABLE IF NOT EXISTS service_tags (
  id VARCHAR(100) PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  category VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_category (category)
);

-- ==========================================
-- SEQUENCES (Otomatik Numara Uretimi)
-- ==========================================
CREATE TABLE IF NOT EXISTS sequences (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type VARCHAR(50) NOT NULL,
  year INT NOT NULL,
  current_value INT NOT NULL DEFAULT 0,
  UNIQUE KEY uk_type_year (type, year)
);

-- ==========================================
-- SYSTEM CONFIG (Admin flags, vb.)
-- ==========================================
CREATE TABLE IF NOT EXISTS system_config (
  config_key VARCHAR(100) PRIMARY KEY,
  config_value TEXT,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
