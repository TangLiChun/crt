INSERT OR IGNORE INTO users (
    id, username, password_hash, display_name, role, is_active, created_at, updated_at
) VALUES
    (
        1,
        'admin',
        '$2y$12$duBZNYCxEE5apPj2fONbzuqSkwlfYCIhzvKdDMbOxi03CQD9iUkJe',
        '销售管理员',
        'manager',
        1,
        '2026-04-15 16:16:44',
        '2026-04-15 16:16:44'
    ),
    (
        2,
        'sales',
        '$2y$12$OFx92Fbm4IScXto4s8U.wOBIBFeoI1VdrkaxQEm6335auCgEqj7RG',
        '陈露',
        'sales',
        1,
        '2026-04-15 16:16:44',
        '2026-04-15 16:16:44'
    ),
    (
        3,
        'sales2',
        '$2y$12$UfHhSgQly4Ia6q7zUx7aMuGcZPanPukS8gxFIsmliLkYsiprYq6CS',
        '王宁',
        'sales',
        1,
        '2026-04-15 16:16:44',
        '2026-04-15 16:16:44'
    );

INSERT OR IGNORE INTO posting_rules (id, channel_name, min_gap_hours, max_gap_hours, is_active, created_at, updated_at) VALUES
    (1, '微信公众号', 72, 168, 1, '2026-04-15 16:16:44', '2026-04-15 16:16:44'),
    (2, '小红书', 48, 120, 1, '2026-04-15 16:16:44', '2026-04-15 16:16:44'),
    (3, '朋友圈', 24, 96, 1, '2026-04-15 16:16:44', '2026-04-15 16:16:44');

INSERT OR IGNORE INTO contacts (
    id, owner_user_id, contact_type, name, company_name, phone, email, source, stage, status, notes,
    last_contacted_at, next_follow_up_at, created_at, updated_at
) VALUES
    (
        1, 2, 'lead', '张晨', '晨曜科技', '13800000001', 'zhangchen@example.com', '活动获客',
        '已跟进', 'active', '对自动化 CRM 有兴趣，关注部署速度。',
        '2026-04-14 10:00:00', '2026-04-16 10:00:00', '2026-04-10 09:30:00', '2026-04-14 10:00:00'
    ),
    (
        2, 2, 'customer', '李颖', '海岚商贸', '13800000002', 'liying@example.com', '转介绍',
        '已成交', 'active', '已成交客户，需定期回访。',
        '2026-04-12 15:30:00', '2026-04-18 09:00:00', '2026-03-28 14:20:00', '2026-04-12 15:30:00'
    ),
    (
        3, 3, 'lead', '周浩', '星旅咨询', '13800000003', 'zhouhao@example.com', '自然来询',
        '待报价', 'active', '对价格敏感，需要补一次方案说明。',
        '2026-04-13 18:10:00', '2026-04-15 14:00:00', '2026-04-08 11:45:00', '2026-04-13 18:10:00'
    ),
    (
        4, 3, 'customer', '赵敏', '和启供应链', '13800000004', 'zhaomin@example.com', '老客户转介绍',
        '已成交', 'active', '需要跟进季度复购计划。',
        '2026-04-11 09:20:00', '2026-04-17 11:00:00', '2026-03-20 10:00:00', '2026-04-11 09:20:00'
    );

INSERT OR IGNORE INTO follow_up_records (id, contact_id, user_id, content, outcome, next_follow_up_at, created_at) VALUES
    (1, 1, 2, '电话沟通了首版需求，对方希望优先解决潜客跟进和发帖节奏。', '准备演示', '2026-04-16 10:00:00', '2026-04-14 10:00:00'),
    (2, 2, 2, '回访老客户，确认目前使用情况稳定。', '一周后继续回访', '2026-04-18 09:00:00', '2026-04-12 15:30:00'),
    (3, 3, 3, '微信补发方案摘要，对方需要内部讨论。', '待报价', '2026-04-15 14:00:00', '2026-04-13 18:10:00'),
    (4, 4, 3, '确认季度采购节奏，客户希望先看优惠方案。', '等待方案', '2026-04-17 11:00:00', '2026-04-11 09:20:00');

INSERT OR IGNORE INTO reminders (
    id, subject_type, subject_id, reminder_type, title, detail, due_at, completed_at, status, assigned_user_id, created_at, updated_at
) VALUES
    (
        1, 'contact', 1, 'follow_up', '跟进 张晨',
        '需要继续推进演示并确认下一步计划。', '2026-04-16 10:00:00', NULL, 'open', 2,
        '2026-04-14 10:00:00', '2026-04-14 10:00:00'
    ),
    (
        2, 'contact', 3, 'follow_up', '报价回访 周浩',
        '今天需要补充报价和说明。', '2026-04-15 14:00:00', NULL, 'open', 3,
        '2026-04-13 18:10:00', '2026-04-13 18:10:00'
    ),
    (
        3, 'contact', 4, 'follow_up', '回访 赵敏',
        '确认季度复购时间与预算。', '2026-04-17 11:00:00', NULL, 'open', 3,
        '2026-04-11 09:20:00', '2026-04-11 09:20:00'
    );

INSERT OR IGNORE INTO marketing_posts (
    id, creator_user_id, posting_rule_id, channel_name, title, content, planned_at, published_at, status, min_gap_hours, created_at, updated_at
) VALUES
    (
        1, 2, 1, '微信公众号', 'CRM 跟进模板分享',
        '介绍如何用统一流程记录潜客跟进。', '2026-04-11 09:00:00', '2026-04-11 09:00:00',
        'published', 72, '2026-04-10 12:00:00', '2026-04-11 09:00:00'
    ),
    (
        2, 2, 1, '微信公众号', '销售回访提醒怎么做',
        '聚焦老客户回访与提醒机制。', '2026-04-15 10:30:00', NULL,
        'planned', 72, '2026-04-14 12:30:00', '2026-04-14 12:30:00'
    ),
    (
        3, 3, 2, '小红书', '销售工作台界面灵感',
        '展示工作台样式与排期节奏。', '2026-04-16 13:00:00', NULL,
        'planned', 48, '2026-04-15 11:00:00', '2026-04-15 11:00:00'
    ),
    (
        4, 3, 3, '朋友圈', '老客户复购沟通模板',
        '分享复购跟进话术。', '2026-04-13 09:00:00', '2026-04-13 09:00:00',
        'published', 24, '2026-04-12 08:30:00', '2026-04-13 09:00:00'
    );
