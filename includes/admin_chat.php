<?php
// admin_chat.php
include '../config/database.php';

// Check admin authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: login.php');
    exit;
}

$pageTitle = "Chat Management - Admin Panel";
include '../includes/header.php';

// Handle admin actions
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'send_admin_message':
            $conversation_id = intval($_POST['conversation_id']);
            $message = trim($_POST['message']);

            if (!empty($message)) {
                $stmt = $pdo->prepare("INSERT INTO chat_messages (conversation_id, message_type, message, admin_id) VALUES (?, 'admin', ?, ?)");
                $stmt->execute([$conversation_id, $message, $_SESSION['user_id']]);

                // Reopen conversation if closed
                $stmt = $pdo->prepare("UPDATE chat_conversations SET status = 'active', assigned_admin = ? WHERE id = ?");
                $stmt->execute([$_SESSION['user_id'], $conversation_id]);

                // Add system message about expert connection
                $stmt = $pdo->prepare("INSERT INTO chat_messages (conversation_id, message_type, message, is_system) VALUES (?, 'system', ?, 1)");
                $stmt->execute([$conversation_id, "🔗 Connected to research expert - " . $_SESSION['full_name']]);

                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Empty message']);
            }
            exit;

        case 'get_conversation_messages':
            $conversation_id = intval($_POST['conversation_id']);
            $stmt = $pdo->prepare("
                SELECT cm.*, a.full_name as admin_name 
                FROM chat_messages cm 
                LEFT JOIN admins a ON cm.admin_id = a.id 
                WHERE cm.conversation_id = ? 
                ORDER BY cm.created_at ASC
            ");
            $stmt->execute([$conversation_id]);
            $messages = $stmt->fetchAll();
            echo json_encode(['success' => true, 'messages' => $messages]);
            exit;

        case 'update_conversation_status':
            $conversation_id = intval($_POST['conversation_id']);
            $status = $_POST['status'];

            $stmt = $pdo->prepare("UPDATE chat_conversations SET status = ? WHERE id = ?");
            $stmt->execute([$status, $conversation_id]);

            echo json_encode(['success' => true]);
            exit;

        case 'assign_to_me':
            $conversation_id = intval($_POST['conversation_id']);
            $stmt = $pdo->prepare("UPDATE chat_conversations SET assigned_admin = ?, status = 'active' WHERE id = ?");
            $stmt->execute([$_SESSION['user_id'], $conversation_id]);
            echo json_encode(['success' => true]);
            exit;

        case 'add_training_data':
            $pattern = trim($_POST['pattern']);
            $response = trim($_POST['response']);
            $category = trim($_POST['category']);

            if (!empty($pattern) && !empty($response)) {
                $stmt = $pdo->prepare("INSERT INTO chat_training_data (question_pattern, response, category, created_by) VALUES (?, ?, ?, ?)");
                $stmt->execute([$pattern, $response, $category, $_SESSION['user_id']]);
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Pattern and response are required']);
            }
            exit;

        case 'toggle_training_data':
            $id = intval($_POST['id']);
            $is_active = intval($_POST['is_active']);
            $stmt = $pdo->prepare("UPDATE chat_training_data SET is_active = ? WHERE id = ?");
            $stmt->execute([$is_active, $id]);
            echo json_encode(['success' => true]);
            exit;

        case 'mark_messages_read':
            $conversation_id = intval($_POST['conversation_id']);
            $stmt = $pdo->prepare("UPDATE chat_messages SET read_status = 1 WHERE conversation_id = ? AND message_type = 'user'");
            $stmt->execute([$conversation_id]);
            echo json_encode(['success' => true]);
            exit;

        case 'get_admin_notifications':
            // Get unread messages count for header
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as unread_count 
                FROM chat_messages cm 
                JOIN chat_conversations cc ON cm.conversation_id = cc.id 
                WHERE cm.message_type = 'user' 
                AND cm.read_status = 0 
                AND cc.assigned_admin = ?
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $unread = $stmt->fetch();
            echo json_encode(['unread_count' => $unread['unread_count']]);
            exit;
    }
}

// Get statistics with enhanced metrics - Fixed query
$stats_stmt = $pdo->query("
    SELECT 
        COUNT(*) as total_conversations,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_conversations,
        SUM(CASE WHEN status = 'waiting' THEN 1 ELSE 0 END) as waiting_conversations,
        SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_conversations,
        SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_conversations,
        (SELECT COUNT(*) FROM chat_messages WHERE message_type = 'user') as total_user_messages,
        (SELECT COUNT(*) FROM chat_messages WHERE message_type = 'ai') as ai_responses,
        (SELECT COUNT(*) FROM chat_messages WHERE message_type = 'user' AND read_status = FALSE) as unread_messages,
        (SELECT COUNT(DISTINCT user_ip) FROM chat_conversations) as unique_visitors
    FROM chat_conversations
");
$stats = $stats_stmt->fetch();

// Get conversations with enhanced data - Fixed query
$conversations_stmt = $pdo->query("
    SELECT c.*, 
           a.full_name as assigned_admin_name,
           (SELECT COUNT(*) FROM chat_messages m WHERE m.conversation_id = c.id AND m.message_type = 'user' AND m.read_status = FALSE) as unread_count,
           (SELECT MAX(created_at) FROM chat_messages WHERE conversation_id = c.id) as last_activity,
           (SELECT message FROM chat_messages WHERE conversation_id = c.id AND message_type = 'user' ORDER BY created_at DESC LIMIT 1) as last_message
    FROM chat_conversations c 
    LEFT JOIN admins a ON c.assigned_admin = a.id
    ORDER BY 
        CASE WHEN c.status = 'active' THEN 1 
             WHEN c.status = 'waiting' THEN 2
             ELSE 3 END,
        last_activity DESC
");
$conversations = $conversations_stmt->fetchAll();

// Get training data
$training_stmt = $pdo->query("SELECT * FROM chat_training_data ORDER BY use_count DESC, category");
$training_data = $training_stmt->fetchAll();

// Get admin performance stats - Fixed query
$performance_stmt = $pdo->query("
    SELECT 
        a.full_name,
        COUNT(DISTINCT cc.id) as assigned_chats,
        COUNT(cm.id) as messages_sent
    FROM admins a
    LEFT JOIN chat_conversations cc ON a.id = cc.assigned_admin
    LEFT JOIN chat_messages cm ON a.id = cm.id
    GROUP BY a.id, a.full_name
");
$performance_stats = $performance_stmt->fetchAll();
?>

<div class="container-fluid py-4">
    <!-- Header with Quick Actions -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gradient text-primary">Chat Management Center</h1>
            <p class="text-muted mb-0">🚀 Manage AI chatbot conversations and provide expert support</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary btn-sm" id="refresh-btn">
                <i class="fas fa-sync-alt me-2"></i>Refresh
            </button>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#quickResponseModal">
                <i class="fas fa-bolt me-2"></i>Quick Responses
            </button>
        </div>
    </div>

    <!-- Enhanced Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card stat-card bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0"><?php echo $stats['total_conversations']; ?></h4>
                            <p class="mb-0 opacity-8">Total Chats</p>
                        </div>
                        <div class="icon-shape">
                            <i class="fas fa-comments fa-2x opacity-7"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card stat-card bg-gradient-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0"><?php echo $stats['active_conversations']; ?></h4>
                            <p class="mb-0 opacity-8">Active Now</p>
                        </div>
                        <div class="icon-shape">
                            <i class="fas fa-circle fa-2x opacity-7"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="badge bg-white text-warning"><?php echo $stats['waiting_conversations']; ?> waiting</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card stat-card bg-gradient-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0"><?php echo $stats['resolved_conversations']; ?></h4>
                            <p class="mb-0 opacity-8">Resolved</p>
                        </div>
                        <div class="icon-shape">
                            <i class="fas fa-check-circle fa-2x opacity-7"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card stat-card bg-gradient-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0"><?php echo $stats['unread_messages']; ?></h4>
                            <p class="mb-0 opacity-8">Unread</p>
                        </div>
                        <div class="icon-shape">
                            <i class="fas fa-envelope fa-2x opacity-7"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <?php if ($stats['unread_messages'] > 0): ?>
                            <span class="badge bg-danger blink">Attention Needed</span>
                        <?php else: ?>
                            <small class="opacity-8">All caught up!</small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card stat-card bg-gradient-secondary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0"><?php echo $stats['ai_responses']; ?></h4>
                            <p class="mb-0 opacity-8">AI Responses</p>
                        </div>
                        <div class="icon-shape">
                            <i class="fas fa-robot fa-2x opacity-7"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card stat-card bg-gradient-dark text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0"><?php echo $stats['unique_visitors']; ?></h4>
                            <p class="mb-0 opacity-8">Unique Visitors</p>
                        </div>
                        <div class="icon-shape">
                            <i class="fas fa-users fa-2x opacity-7"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Conversations List -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">💬 Active Conversations</h5>
                    <span class="badge bg-primary"><?php echo count($conversations); ?> total</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" id="conversations-list">
                        <?php if (empty($conversations)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No conversations yet</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($conversations as $conv): ?>
                                <a href="#" class="list-group-item list-group-item-action conversation-item" data-id="<?php echo $conv['id']; ?>">
                                    <div class="d-flex align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex w-100 justify-content-between align-items-start mb-1">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-user me-1 text-muted"></i>
                                                    <?php echo htmlspecialchars($conv['user_ip']); ?>
                                                    <?php if ($conv['unread_count'] > 0): ?>
                                                        <span class="badge bg-danger ms-2 blink"><?php echo $conv['unread_count']; ?> new</span>
                                                    <?php endif; ?>
                                                </h6>
                                                <small class="text-muted"><?php echo date('g:i A', strtotime($conv['last_activity'])); ?></small>
                                            </div>

                                            <!-- Last Message Preview -->
                                            <?php if ($conv['last_message']): ?>
                                                <p class="mb-1 text-muted small text-truncate">
                                                    <?php echo htmlspecialchars(substr($conv['last_message'], 0, 60)); ?>
                                                    <?php if (strlen($conv['last_message']) > 60): ?>...<?php endif; ?>
                                                </p>
                                            <?php endif; ?>

                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="badge status-badge bg-<?php
                                                                                    echo $conv['status'] == 'active' ? 'warning' : ($conv['status'] == 'waiting' ? 'info' : ($conv['status'] == 'resolved' ? 'success' : 'secondary'));
                                                                                    ?>">
                                                    <i class="fas fa-<?php
                                                                        echo $conv['status'] == 'active' ? 'circle' : ($conv['status'] == 'waiting' ? 'clock' : ($conv['status'] == 'resolved' ? 'check' : 'times'));
                                                                        ?> me-1"></i>
                                                    <?php echo ucfirst($conv['status']); ?>
                                                </span>

                                                <?php if ($conv['assigned_admin_name']): ?>
                                                    <small class="text-muted" title="Assigned to <?php echo $conv['assigned_admin_name']; ?>">
                                                        <i class="fas fa-user-shield"></i>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Interface -->
        <div class="col-lg-5 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center bg-light">
                    <div>
                        <h5 class="mb-0" id="conversation-title">
                            <i class="fas fa-comments me-2 text-primary"></i>
                            <span>Select a Conversation</span>
                        </h5>
                        <small class="text-muted" id="conversation-subtitle">Click on a conversation to start chatting</small>
                    </div>
                    <div id="conversation-actions" style="display: none;" class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-sm" id="assign-to-me-btn">
                            <i class="fas fa-user-plus me-1"></i>Assign to Me
                        </button>
                        <select class="form-select form-select-sm" id="status-select" style="width: auto;">
                            <option value="waiting">⏳ Waiting</option>
                            <option value="active">🔵 Active</option>
                            <option value="resolved">✅ Resolved</option>
                            <option value="closed">🔒 Closed</option>
                        </select>
                    </div>
                </div>
                <div class="card-body d-flex flex-column p-0">
                    <div id="admin-chat-messages" class="admin-chat-messages flex-grow-1">
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-comments fa-4x mb-3 opacity-3"></i>
                            <h5>No Conversation Selected</h5>
                            <p>Choose a conversation from the list to view and respond to messages</p>
                        </div>
                    </div>
                    <div class="admin-chat-input p-3 border-top" style="display: none;">
                        <div class="input-group">
                            <button class="btn btn-outline-secondary" type="button" id="quick-responses-btn">
                                <i class="fas fa-bolt"></i>
                            </button>
                            <input type="text" id="admin-message-input" class="form-control" placeholder="Type your response to the user..." maxlength="1000">
                            <button id="admin-send-btn" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Send
                            </button>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Your response will be sent directly to the user
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Sidebar - Training & Analytics -->
        <div class="col-lg-3 mb-4">
            <!-- Training Data Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">🤖 AI Training Data</h5>
                </div>
                <div class="card-body">
                    <form id="training-form" class="mb-3">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Question Patterns</label>
                            <input type="text" class="form-control form-control-sm" name="pattern" placeholder="hello|hi|hey" required>
                            <small class="text-muted">Separate patterns with |</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">AI Response</label>
                            <textarea class="form-control form-control-sm" name="response" rows="2" placeholder="Enter the AI's response..." required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Category</label>
                            <select class="form-select form-select-sm" name="category">
                                <option value="greeting">👋 Greeting</option>
                                <option value="pricing">💰 Pricing</option>
                                <option value="services">🎯 Services</option>
                                <option value="support">🆘 Support</option>
                                <option value="technical">🔧 Technical</option>
                                <option value="other">📁 Other</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-plus me-1"></i> Add Training Data
                        </button>
                    </form>

                    <div class="training-list" style="max-height: 300px; overflow-y: auto;">
                        <h6 class="small fw-bold mb-3">Active Training Data</h6>
                        <?php if (empty($training_data)): ?>
                            <p class="text-muted small">No training data yet. Add some to improve the AI responses.</p>
                        <?php else: ?>
                            <?php foreach ($training_data as $training): ?>
                                <div class="training-item mb-2 p-2 border rounded <?php echo $training['is_active'] ? 'border-success' : 'border-secondary'; ?>">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <small class="text-muted fw-bold"><?php echo $training['category']; ?></small>
                                                <span class="badge bg-<?php echo $training['is_active'] ? 'success' : 'secondary'; ?>">
                                                    <?php echo $training['use_count']; ?> uses
                                                </span>
                                            </div>
                                            <p class="mb-1 small"><?php echo htmlspecialchars($training['question_pattern']); ?></p>
                                            <p class="mb-1 small text-primary"><?php echo htmlspecialchars($training['response']); ?></p>
                                        </div>
                                        <div class="ms-2">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input training-toggle" type="checkbox"
                                                    data-id="<?php echo $training['id']; ?>"
                                                    <?php echo $training['is_active'] ? 'checked' : ''; ?>>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Performance Stats -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">📊 Team Performance</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($performance_stats)): ?>
                        <p class="text-muted small">No performance data available yet.</p>
                    <?php else: ?>
                        <?php foreach ($performance_stats as $perf): ?>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="mb-0 small"><?php echo $perf['full_name']; ?></h6>
                                    <small class="text-muted"><?php echo $perf['assigned_chats']; ?> chats</small>
                                </div>
                                <div class="text-end">
                                    <small class="text-success"><?php echo $perf['messages_sent']; ?> msgs</small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Response Modal -->
<div class="modal fade" id="quickResponseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">🚀 Quick Responses</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2">
                    <div class="col-6">
                        <button class="btn btn-outline-primary w-100 quick-response" data-response="Hello! I'm [Your Name], a research expert from KBMO Center. How can I assist you with your research today?">
                            👋 Introduction
                        </button>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-outline-primary w-100 quick-response" data-response="I'd be happy to help you with that! Could you provide more details about your research project?">
                            🔍 More Details
                        </button>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-outline-primary w-100 quick-response" data-response="For pricing information, our research proposals start at $199, data analysis from $149, and thesis writing from $299. Would you like a custom quote?">
                            💰 Pricing Info
                        </button>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-outline-primary w-100 quick-response" data-response="We can schedule a free consultation call to discuss your project in detail. What time works best for you?">
                            📅 Schedule Call
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .stat-card {
        border: none;
        border-radius: 12px;
        transition: transform 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
    }

    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .bg-gradient-warning {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .bg-gradient-success {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .bg-gradient-info {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    }

    .bg-gradient-secondary {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    }

    .bg-gradient-dark {
        background: linear-gradient(135deg, #4c4177 0%, #2a5470 100%);
    }

    .icon-shape {
        width: 50px;
        height: 50px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .admin-chat-messages {
        height: 400px;
        overflow-y: auto;
        padding: 20px;
        background: #f8f9fa;
    }

    .message-bubble {
        max-width: 80%;
        padding: 12px 16px;
        border-radius: 18px;
        margin-bottom: 8px;
        position: relative;
    }

    .message-user .message-bubble {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        margin-left: auto;
        border-bottom-right-radius: 5px;
    }

    .message-admin .message-bubble {
        background: white;
        border: 1px solid #e9ecef;
        border-bottom-left-radius: 5px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .message-system .message-bubble {
        background: #e7f3ff;
        border: 1px solid #b3d9ff;
        color: #0066cc;
        text-align: center;
        max-width: 90%;
        margin: 10px auto;
        font-style: italic;
    }

    .message-ai .message-bubble {
        background: #f0f7ff;
        border: 1px solid #cce5ff;
        color: #004085;
        border-bottom-left-radius: 5px;
    }

    .conversation-item {
        border: none;
        border-bottom: 1px solid #f1f3f4;
        transition: all 0.2s;
    }

    .conversation-item:hover {
        background-color: #f8f9fa;
        transform: translateX(2px);
    }

    .conversation-item.active {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border-color: #667eea;
    }

    .conversation-item.active .text-muted {
        color: rgba(255, 255, 255, 0.8) !important;
    }

    .status-badge {
        font-size: 0.7rem;
        padding: 4px 8px;
        border-radius: 12px;
    }

    .blink {
        animation: blink 2s infinite;
    }

    @keyframes blink {

        0%,
        50% {
            opacity: 1;
        }

        51%,
        100% {
            opacity: 0.5;
        }
    }

    .training-item {
        transition: all 0.2s;
    }

    .training-item:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .text-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
</style>

<script>
    // JavaScript remains the same as in the previous version
    let currentConversationId = null;
    let autoRefreshInterval = null;

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize auto-refresh
        startAutoRefresh();

        // Conversation selection
        document.querySelectorAll('.conversation-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                selectConversation(this);
            });
        });

        // Send admin message
        document.getElementById('admin-send-btn').addEventListener('click', sendAdminMessage);
        document.getElementById('admin-message-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendAdminMessage();
            }
        });

        // Status change
        document.getElementById('status-select').addEventListener('change', function() {
            if (currentConversationId) {
                updateConversationStatus(currentConversationId, this.value);
            }
        });

        // Assign to me
        document.getElementById('assign-to-me-btn').addEventListener('click', function() {
            if (currentConversationId) {
                assignToMe(currentConversationId);
            }
        });

        // Add training data
        document.getElementById('training-form').addEventListener('submit', function(e) {
            e.preventDefault();
            addTrainingData();
        });

        // Toggle training data
        document.querySelectorAll('.training-toggle').forEach(toggle => {
            toggle.addEventListener('change', function() {
                toggleTrainingData(this.getAttribute('data-id'), this.checked);
            });
        });

        // Quick responses
        document.querySelectorAll('.quick-response').forEach(btn => {
            btn.addEventListener('click', function() {
                const response = this.getAttribute('data-response');
                document.getElementById('admin-message-input').value = response;
                $('#quickResponseModal').modal('hide');
            });
        });

        // Refresh button
        document.getElementById('refresh-btn').addEventListener('click', function() {
            location.reload();
        });

        // Quick responses button
        document.getElementById('quick-responses-btn').addEventListener('click', function() {
            $('#quickResponseModal').modal('show');
        });
    });

    function selectConversation(item) {
        // Remove active class from all items
        document.querySelectorAll('.conversation-item').forEach(i => i.classList.remove('active'));

        // Add active class to clicked item
        item.classList.add('active');

        // Load conversation
        const convId = item.getAttribute('data-id');
        loadConversation(convId);
    }

    function loadConversation(convId) {
        currentConversationId = convId;

        // Show conversation actions
        document.getElementById('conversation-actions').style.display = 'flex';
        document.getElementById('conversation-title').innerHTML = '<i class="fas fa-comments me-2 text-primary"></i>Conversation #' + convId;
        document.querySelector('.admin-chat-input').style.display = 'block';

        // Load messages
        fetch('./admin_chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_conversation_messages&conversation_id=' + convId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayMessages(data.messages);
                    markMessagesAsRead(convId);
                    updateUnreadCount();
                }
            });
    }

    function displayMessages(messages) {
        const container = document.getElementById('admin-chat-messages');
        container.innerHTML = '';

        if (messages.length === 0) {
            container.innerHTML = '<div class="text-center text-muted py-4"><p>No messages yet</p></div>';
            return;
        }

        messages.forEach(msg => {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message-${msg.message_type} mb-3`;

            const bubble = document.createElement('div');
            bubble.className = 'message-bubble';

            // Add admin name if available
            if (msg.message_type === 'admin' && msg.admin_name) {
                const nameSpan = document.createElement('div');
                nameSpan.className = 'small fw-bold mb-1';
                nameSpan.textContent = msg.admin_name;
                bubble.appendChild(nameSpan);
            }

            const content = document.createElement('div');
            content.textContent = msg.message;
            bubble.appendChild(content);

            const time = document.createElement('div');
            time.className = 'small text-muted mt-1';
            time.textContent = formatMessageTime(msg.created_at);

            messageDiv.appendChild(bubble);
            messageDiv.appendChild(time);
            container.appendChild(messageDiv);
        });

        // Scroll to bottom
        container.scrollTop = container.scrollHeight;
    }

    function formatMessageTime(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);

        if (diffMins < 1) return 'Just now';
        if (diffMins < 60) return `${diffMins}m ago`;
        if (diffHours < 24) return `${diffHours}h ago`;
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function sendAdminMessage() {
        const message = document.getElementById('admin-message-input').value.trim();
        if (!message || !currentConversationId) return;

        fetch('./admin_chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=send_admin_message&conversation_id=' + currentConversationId + '&message=' + encodeURIComponent(message)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('admin-message-input').value = '';
                    loadConversation(currentConversationId);
                }
            });
    }

    function updateConversationStatus(convId, status) {
        fetch('./admin_chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=update_conversation_status&conversation_id=' + convId + '&status=' + status
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update UI
                    const activeItem = document.querySelector('.conversation-item.active');
                    if (activeItem) {
                        const badge = activeItem.querySelector('.status-badge');
                        badge.className = `status-badge bg-${getStatusColor(status)}`;
                        badge.innerHTML = `<i class="fas fa-${getStatusIcon(status)} me-1"></i>${status}`;
                    }
                }
            });
    }

    function assignToMe(convId) {
        fetch('./admin_chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=assign_to_me&conversation_id=' + convId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Conversation assigned to you!', 'success');
                }
            });
    }

    function getStatusColor(status) {
        const colors = {
            'active': 'warning',
            'waiting': 'info',
            'resolved': 'success',
            'closed': 'secondary'
        };
        return colors[status] || 'secondary';
    }

    function getStatusIcon(status) {
        const icons = {
            'active': 'circle',
            'waiting': 'clock',
            'resolved': 'check',
            'closed': 'times'
        };
        return icons[status] || 'circle';
    }

    function addTrainingData() {
        const form = document.getElementById('training-form');
        const formData = new FormData(form);

        fetch('./admin_chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=add_training_data&pattern=' + encodeURIComponent(formData.get('pattern')) +
                    '&response=' + encodeURIComponent(formData.get('response')) +
                    '&category=' + encodeURIComponent(formData.get('category'))
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    form.reset();
                    showToast('Training data added successfully!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('Error: ' + data.error, 'error');
                }
            });
    }

    function toggleTrainingData(id, isActive) {
        fetch('./admin_chat.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=toggle_training_data&id=' + id + '&is_active=' + (isActive ? '1' : '0')
        }).then(() => {
            showToast('Training data ' + (isActive ? 'activated' : 'deactivated'), 'success');
        });
    }

    function markMessagesAsRead(convId) {
        fetch('./admin_chat.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=mark_messages_read&conversation_id=' + convId
        });
    }

    function updateUnreadCount() {
        fetch('./admin_chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_admin_notifications'
            })
            .then(response => response.json())
            .then(data => {
                // Update header notification badge if exists
                const headerBadge = document.getElementById('admin-notification-badge');
                if (headerBadge) {
                    if (data.unread_count > 0) {
                        headerBadge.textContent = data.unread_count;
                        headerBadge.style.display = 'inline';
                    } else {
                        headerBadge.style.display = 'none';
                    }
                }
            });
    }

    function startAutoRefresh() {
        autoRefreshInterval = setInterval(() => {
            if (currentConversationId) {
                loadConversation(currentConversationId);
            }
            updateUnreadCount();
        }, 10000); // Refresh every 10 seconds
    }

    function showToast(message, type = 'info') {
        // Simple toast implementation
        const toast = document.createElement('div');
        toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
         ${message}
         <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
     `;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    // Initialize unread count
    updateUnreadCount();
</script>

<?php include '../includes/footer.php'; ?>