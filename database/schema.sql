CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    display_name TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'manager',
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS contacts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    owner_user_id INTEGER NOT NULL,
    contact_type TEXT NOT NULL CHECK (contact_type IN ('lead', 'customer')),
    name TEXT NOT NULL,
    company_name TEXT,
    phone TEXT,
    email TEXT,
    source TEXT,
    stage TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'active',
    notes TEXT,
    last_contacted_at TEXT,
    next_follow_up_at TEXT,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL,
    FOREIGN KEY (owner_user_id) REFERENCES users(id)
);

CREATE INDEX IF NOT EXISTS idx_contacts_type_stage ON contacts(contact_type, stage);
CREATE INDEX IF NOT EXISTS idx_contacts_next_follow_up ON contacts(next_follow_up_at);

CREATE TABLE IF NOT EXISTS follow_up_records (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    contact_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    content TEXT NOT NULL,
    outcome TEXT,
    next_follow_up_at TEXT,
    created_at TEXT NOT NULL,
    FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE INDEX IF NOT EXISTS idx_follow_ups_contact_id ON follow_up_records(contact_id, created_at DESC);

CREATE TABLE IF NOT EXISTS posting_rules (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    channel_name TEXT NOT NULL UNIQUE,
    min_gap_hours INTEGER NOT NULL,
    max_gap_hours INTEGER NOT NULL,
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS marketing_posts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    creator_user_id INTEGER NOT NULL,
    posting_rule_id INTEGER,
    channel_name TEXT NOT NULL,
    title TEXT NOT NULL,
    content TEXT,
    planned_at TEXT NOT NULL,
    published_at TEXT,
    status TEXT NOT NULL DEFAULT 'planned',
    min_gap_hours INTEGER NOT NULL DEFAULT 72,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL,
    FOREIGN KEY (creator_user_id) REFERENCES users(id),
    FOREIGN KEY (posting_rule_id) REFERENCES posting_rules(id)
);

CREATE INDEX IF NOT EXISTS idx_marketing_posts_channel_time ON marketing_posts(channel_name, planned_at);

CREATE TABLE IF NOT EXISTS reminders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    subject_type TEXT NOT NULL,
    subject_id INTEGER NOT NULL,
    reminder_type TEXT NOT NULL,
    title TEXT NOT NULL,
    detail TEXT,
    due_at TEXT NOT NULL,
    completed_at TEXT,
    status TEXT NOT NULL DEFAULT 'open',
    assigned_user_id INTEGER NOT NULL,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL,
    FOREIGN KEY (assigned_user_id) REFERENCES users(id)
);

CREATE INDEX IF NOT EXISTS idx_reminders_due_status ON reminders(status, due_at);
CREATE UNIQUE INDEX IF NOT EXISTS idx_reminders_unique_open ON reminders(subject_type, subject_id, reminder_type, status);
