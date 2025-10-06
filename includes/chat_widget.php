<?php
// includes/chat_widget.php
// Turn off error display for production
error_reporting(0);
ini_set('display_errors', 0);

// Set headers to prevent CORS issues
header("Access-Control-Allow-Origin: " . ($_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_HOST'] ?? '*'));
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Sample research projects data
$sampleProjects = [
    'health' => [
        'title' => "🏥 Impact of Telemedicine on Rural Healthcare Access",
        'description' => "A comprehensive study on how telemedicine improves healthcare delivery in remote areas of Northern Uganda.",
        'pages' => "45 pages",
        'methodology' => "Mixed-methods approach",
        'completion' => "6 months"
    ],
    'education' => [
        'title' => "📚 Digital Learning Platforms and Student Performance",
        'description' => "Analysis of e-learning effectiveness in secondary schools during and post-COVID-19 pandemic.",
        'pages' => "62 pages",
        'methodology' => "Quantitative survey research",
        'completion' => "4 months"
    ],
    'business' => [
        'title' => "💼 SME Digital Transformation in Developing Economies",
        'description' => "Case studies on technology adoption among small and medium enterprises in East Africa.",
        'pages' => "38 pages",
        'methodology' => "Case study analysis",
        'completion' => "5 months"
    ],
    'environment' => [
        'title' => "🌿 Climate Change Adaptation Strategies for Agriculture",
        'description' => "Evaluating sustainable farming practices in response to changing weather patterns.",
        'pages' => "55 pages",
        'methodology' => "Action research",
        'completion' => "8 months"
    ]
];

// Handle AJAX requests first
if (isset($_POST['action'])) {
    // Set content type for AJAX responses
    header('Content-Type: application/json');

    $action = $_POST['action'];

    switch ($action) {
        case 'send_message':
            $message = trim($_POST['message'] ?? '');
            $session_id = $_POST['session_id'] ?? uniqid('chat_');

            if (empty($message)) {
                echo json_encode(['success' => false, 'error' => 'Empty message']);
                exit;
            }

            // Process message with enhanced bot
            $response = processChatMessage($message, $session_id);
            echo json_encode([
                'success' => true,
                'response' => $response['text'],
                'actions' => $response['actions'] ?? [],
                'session_id' => $session_id
            ]);
            exit;

        case 'get_samples':
            $category = $_POST['category'] ?? 'health';
            $samples = getSampleProjects($category);
            echo json_encode(['success' => true, 'samples' => $samples]);
            exit;

        case 'start_project':
            $project_type = $_POST['project_type'] ?? '';
            $user_email = $_POST['user_email'] ?? '';
            echo json_encode([
                'success' => true,
                'message' => "🚀 Excellent! Let's get your $project_type started!",
                'next_steps' => [
                    "📧 We've sent project details to $user_email",
                    "📋 Schedule a free consultation call",
                    "💳 Secure your project with 30% deposit",
                    "📤 Upload your existing materials"
                ]
            ]);
            exit;

        case 'get_history':
            $session_id = $_POST['session_id'] ?? '';
            echo json_encode(['success' => true, 'history' => getChatHistory($session_id)]);
            exit;

        case 'close_chat':
            echo json_encode(['success' => true]);
            exit;

        default:
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
            exit;
    }
}

function processChatMessage($message, $session_id)
{
    $message = strtolower(trim($message));
    $response = [
        'text' => '',
        'actions' => [],
        'type' => 'text'
    ];

    // Enhanced pattern matching with emoji detection and sentiment
    $sentiment = detectSentiment($message);
    $emoji = getContextEmoji($message, $sentiment);

    // Enhanced responses with dynamic actions
    $responses = [
        'greeting' => [
            'patterns' => ['hello', 'hi', 'hey', 'greetings', 'good morning', 'good afternoon', 'good evening'],
            'response' => "{$emoji} Hello! Welcome to KBMO Center for Translational Research! 🌟 I'm your AI research assistant. How can I help you with your research journey today?",
            'actions' => [
                ['text' => '📊 Research Services', 'value' => 'services'],
                ['text' => '💰 Pricing Info', 'value' => 'pricing'],
                ['text' => '🚀 Start Project', 'value' => 'start_project']
            ]
        ],

        'samples' => [
            'patterns' => ['sample', 'example', 'portfolio', 'previous work', 'show me', 'see work', 'past projects'],
            'response' => "{$emoji} Absolutely! Here are some sample research projects from our portfolio:",
            'actions' => [
                ['text' => '🏥 Health Research', 'value' => 'health_samples'],
                ['text' => '📚 Education Studies', 'value' => 'education_samples'],
                ['text' => '💼 Business Research', 'value' => 'business_samples'],
                ['text' => '🌿 Environmental Studies', 'value' => 'environment_samples']
            ]
        ],

        'start_research' => [
            'patterns' => ['start research', 'begin project', 'want to start', 'need research', 'help me research'],
            'response' => "{$emoji} 🎉 Fantastic! Let's get your research project started! I'll guide you through the process step by step.",
            'actions' => [
                ['text' => '📋 Research Proposal', 'value' => 'start_proposal'],
                ['text' => '📊 Data Analysis', 'value' => 'start_analysis'],
                ['text' => '📝 Thesis Writing', 'value' => 'start_thesis'],
                ['text' => '🔍 Literature Review', 'value' => 'start_literature']
            ]
        ],

        'upload_docs' => [
            'patterns' => ['upload', 'document', 'file', 'materials', 'existing work', 'draft'],
            'response' => "{$emoji} 📤 Great! You can upload your research materials through our secure portal. This helps us understand your project better and provide more accurate assistance.",
            'actions' => [
                ['text' => '📎 Upload Documents', 'value' => 'upload_documents'],
                ['text' => '🔒 Secure Portal', 'value' => 'secure_upload'],
                ['text' => '📞 Get Help Uploading', 'value' => 'upload_help']
            ]
        ],

        'payment' => [
            'patterns' => ['payment', 'pay', 'cost', 'price', 'fee', 'how much', 'budget'],
            'response' => "{$emoji} 💳 We offer flexible payment options to suit your needs:\n\n• 30% advance to start project\n• 40% after first draft\n• 30% upon final delivery\n• Package discounts available\n• Installment plans possible",
            'actions' => [
                ['text' => '💵 Get Quote', 'value' => 'get_quote'],
                ['text' => '📅 Payment Plan', 'value' => 'payment_plan'],
                ['text' => '🎁 Package Deals', 'value' => 'packages']
            ]
        ],

        'signup' => [
            'patterns' => ['sign up', 'register', 'create account', 'new user', 'join'],
            'response' => "{$emoji} 📝 Ready to begin your research journey? Creating an account takes just 2 minutes and gives you:\n\n• 🔒 Secure project space\n• 📁 File management\n• 🎯 Progress tracking\n• 💬 Direct expert communication",
            'actions' => [
                ['text' => '👤 Create Account', 'value' => 'create_account'],
                ['text' => '📋 Quick Registration', 'value' => 'quick_register'],
                ['text' => 'ℹ️ Learn More', 'value' => 'signup_info']
            ]
        ],

        'deadline' => [
            'patterns' => ['deadline', 'urgent', 'fast', 'quick', 'asap', 'time', 'when ready'],
            'response' => "{$emoji} ⏰ We understand timing is crucial! Here are our standard timelines:\n\n⚡ Express Service (48-72 hours)\n🚀 Standard Service (1-2 weeks)\n📅 Comprehensive (3-4 weeks)\n\nWe can accommodate urgent projects with priority processing!",
            'actions' => [
                ['text' => '⚡ Express Service', 'value' => 'express_service'],
                ['text' => '🚀 Standard Timeline', 'value' => 'standard_timeline'],
                ['text' => '📞 Urgent Help', 'value' => 'urgent_help']
            ]
        ],

        'expert_help' => [
            'patterns' => ['expert', 'specialist', 'consultant', 'talk to human', 'real person'],
            'response' => "{$emoji} 👨‍💼 I'd be happy to connect you with one of our research experts! Our team includes:\n\n• PhD holders in various fields\n• Seasoned academic writers\n• Statistical analysis experts\n• Journal publication specialists",
            'actions' => [
                ['text' => '📞 Call Expert', 'value' => 'call_expert'],
                ['text' => '📅 Book Consultation', 'value' => 'book_consultation'],
                ['text' => '💬 Live Chat', 'value' => 'live_chat']
            ]
        ]
    ];

    // Check for matches
    foreach ($responses as $category => $data) {
        foreach ($data['patterns'] as $pattern) {
            if (!empty($pattern) && strpos($message, $pattern) !== false) {
                $response['text'] = $data['response'];
                $response['actions'] = $data['actions'];
                return $response;
            }
        }
    }

    // Dynamic response generation for unknown queries
    $response['text'] = generateDynamicResponse($message, $sentiment, $emoji);
    $response['actions'] = [
        ['text' => '📞 Call for Help', 'value' => 'call_support'],
        ['text' => '💬 Live Expert', 'value' => 'live_expert'],
        ['text' => '📋 Start Project', 'value' => 'start_general']
    ];

    return $response;
}

function detectSentiment($message)
{
    $positive_words = ['good', 'great', 'excellent', 'awesome', 'perfect', 'love', 'thanks', 'thank you', 'amazing', 'wonderful', 'happy'];
    $negative_words = ['bad', 'terrible', 'awful', 'hate', 'angry', 'frustrated', 'disappointed', 'poor', 'worst', 'urgent', 'emergency'];

    $positive_count = 0;
    $negative_count = 0;

    foreach ($positive_words as $word) {
        if (strpos($message, $word) !== false) $positive_count++;
    }

    foreach ($negative_words as $word) {
        if (strpos($message, $word) !== false) $negative_count++;
    }

    if ($positive_count > $negative_count) return 'positive';
    if ($negative_count > $positive_count) return 'negative';
    return 'neutral';
}

function getContextEmoji($message, $sentiment)
{
    // Context-based emoji matching
    $emoji_map = [
        'hello' => '👋',
        'hi' => '👋',
        'hey' => '👋',
        'thanks' => '🙏',
        'thank you' => '🙏',
        'help' => '🆘',
        'urgent' => '🚨',
        'emergency' => '🚨',
        'research' => '🔍',
        'study' => '📚',
        'thesis' => '🎓',
        'data' => '📊',
        'analysis' => '📈',
        'statistics' => '📊',
        'price' => '💵',
        'cost' => '💰',
        'payment' => '💳',
        'time' => '⏰',
        'deadline' => '📅',
        'when' => '🕒',
        'upload' => '📤',
        'document' => '📎',
        'file' => '📁',
        'sample' => '📄',
        'example' => '🔍',
        'portfolio' => '📂'
    ];

    foreach ($emoji_map as $word => $emoji) {
        if (strpos($message, $word) !== false) {
            return $emoji;
        }
    }

    // Sentiment-based fallback emoji
    return $sentiment == 'positive' ? '😊' : ($sentiment == 'negative' ? '😔' : '🤔');
}

function generateDynamicResponse($message, $sentiment, $emoji)
{
    $dynamic_responses = [
        "{$emoji} I understand you're interested in \"$message\". This sounds like a specialized research area where our experts can provide significant value!",
        "{$emoji} Thank you for your query about \"$message\"! Our research team has extensive experience in this domain and can help you achieve excellent results.",
        "{$emoji} Regarding \"$message\", I recommend consulting with one of our subject matter specialists who can provide detailed guidance tailored to your specific needs.",
        "{$emoji} That's an interesting research topic! For comprehensive assistance with \"$message\", our team can develop a customized approach for your project."
    ];

    $response = $dynamic_responses[array_rand($dynamic_responses)];
    $response .= "\n\n🔍 **Next Steps:**\n• Schedule a free consultation\n• Discuss your specific requirements\n• Receive a customized project plan\n• Get started within 24 hours";

    return $response;
}

function getSampleProjects($category)
{
    global $sampleProjects;
    return $sampleProjects[$category] ?? $sampleProjects['health'];
}

function getChatHistory($session_id)
{
    // In a real implementation, this would fetch from database
    return [];
}
?>

<!-- Enhanced Chat Widget HTML -->
<div id="chat-widget" class="chat-widget">
    <div id="chat-header" class="chat-header">
        <div class="d-flex align-items-center">
            <div class="chat-avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div class="chat-info">
                <h6 class="mb-0">KBMO Research Assistant</h6>
                <small class="status-online"><i class="fas fa-circle"></i> Online</small>
            </div>
        </div>
        <div class="chat-header-actions">
            <button class="btn-chat-action" title="Clear Chat" id="chat-clear">
                <i class="fas fa-trash"></i>
            </button>
            <button class="btn-chat-action" title="Minimize" id="chat-minimize">
                <i class="fas fa-minus"></i>
            </button>
            <button id="chat-close" class="btn-chat-action" title="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <div id="chat-messages" class="chat-messages">
        <div class="chat-welcome">
            <div class="ai-message">
                <div class="message-bubble">
                    <div class="welcome-header">
                        <span class="welcome-emoji">🎓</span>
                        <h6>Welcome to KBMO Research Center!</h6>
                    </div>
                    <p class="mb-2">I'm your AI research assistant, here to help you with:</p>
                    <div class="service-grid">
                        <div class="service-item">
                            <span class="service-emoji">📊</span>
                            <span>Research Proposals</span>
                        </div>
                        <div class="service-item">
                            <span class="service-emoji">📈</span>
                            <span>Data Analysis</span>
                        </div>
                        <div class="service-item">
                            <span class="service-emoji">📝</span>
                            <span>Thesis Writing</span>
                        </div>
                        <div class="service-item">
                            <span class="service-emoji">🔍</span>
                            <span>Literature Review</span>
                        </div>
                    </div>
                    <p class="mt-2 mb-1">🚀 <strong>Quick Start Options:</strong></p>
                    <div class="quick-actions-grid">
                        <button class="quick-action-btn" data-action="start_project">
                            <span>🚀 Start Project</span>
                        </button>
                        <button class="quick-action-btn" data-action="view_samples">
                            <span>📄 View Samples</span>
                        </button>
                        <button class="quick-action-btn" data-action="get_pricing">
                            <span>💰 Get Pricing</span>
                        </button>
                        <button class="quick-action-btn" data-action="talk_expert">
                            <span>👨‍💼 Talk to Expert</span>
                        </button>
                    </div>
                </div>
                <small class="message-time">Just now</small>
            </div>
        </div>
    </div>

    <div class="chat-input-container">
        <div class="typing-indicator" id="typing-indicator">
            <div class="typing-content">
                <span>KBMO Assistant is typing</span>
                <div class="typing-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>

        <div class="action-buttons" id="action-buttons">
            <!-- Dynamic action buttons will appear here -->
        </div>

        <div class="input-group">
            <button class="btn-attachment" id="chat-attachment" title="Attach files">
                <i class="fas fa-paperclip"></i>
            </button>
            <input type="text" id="chat-input" class="form-control" placeholder="Describe your research project or ask a question..." maxlength="1000">
            <button id="chat-send" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>

        <div class="quick-suggestions">
            <div class="suggestion-title">💡 Quick Suggestions:</div>
            <div class="suggestion-chips">
                <button class="suggestion-chip" data-message="Show me research samples">📄 Research Samples</button>
                <button class="suggestion-chip" data-message="I need help with data analysis">📊 Data Analysis</button>
                <button class="suggestion-chip" data-message="How to start my thesis">📝 Thesis Help</button>
                <button class="suggestion-chip" data-message="What's the process for research proposal">🔍 Proposal Process</button>
            </div>
        </div>
    </div>
</div>

<button id="chat-toggle" class="chat-toggle-btn">
    <i class="fas fa-comments"></i>
    <span class="chat-notification" id="chat-notification"></span>
</button>

<style>
    /* ...existing code... */
    .chat-widget {
        position: fixed;
        bottom: 30px;
        right: 20px;
        width: 750px;
        /* Increased width */
        height: 750px;
        /* Increased height */
        background: white;
        border-radius: 20px;
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
        display: flex;
        flex-direction: column;
        z-index: 10000;
        transform: translateY(20px) scale(0.95);
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        border: 1px solid #e1e5e9;
    }

    /* ...existing code... */

    .chat-widget.active {
        transform: translateY(0) scale(1);
        opacity: 1;
        visibility: visible;
    }

    .chat-widget.minimized {
        height: 70px;
        overflow: hidden;
    }

    .chat-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 20px;
        border-radius: 20px 20px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
    }

    .chat-header-actions {
        display: flex;
        gap: 5px;
    }

    .btn-chat-action {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-chat-action:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: scale(1.1);
    }

    .chat-avatar {
        width: 45px;
        height: 45px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        font-size: 1.2em;
    }

    .chat-info h6 {
        font-weight: 700;
        margin: 0;
        font-size: 1.1rem;
    }

    .status-online {
        font-size: 0.75em;
        opacity: 0.9;
        display: flex;
        align-items: center;
        margin: 0;
    }

    .status-online .fas {
        color: #4ade80;
        font-size: 0.6em;
        margin-right: 5px;
    }

    .chat-messages {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
    }

    .welcome-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 15px;
    }

    .welcome-emoji {
        font-size: 1.5em;
    }

    .welcome-header h6 {
        margin: 0;
        color: #374151;
    }

    .service-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin: 15px 0;
    }

    .service-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        background: #f1f5f9;
        border-radius: 10px;
        font-size: 0.85em;
    }

    .service-emoji {
        font-size: 1.1em;
    }

    .quick-actions-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin: 15px 0;
    }

    .quick-action-btn {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border: none;
        padding: 10px;
        border-radius: 12px;
        font-size: 0.8em;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
    }

    .quick-action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    .message-bubble {
        background: white;
        padding: 15px 18px;
        border-radius: 20px;
        margin-bottom: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        max-width: 90%;
        word-wrap: break-word;
        line-height: 1.5;
    }

    .user-message .message-bubble {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border-bottom-right-radius: 5px;
    }

    .ai-message .message-bubble {
        background: white;
        border: 1px solid #e2e8f0;
        border-bottom-left-radius: 5px;
    }

    .message-time {
        font-size: 0.7em;
        color: #64748b;
        margin-top: 5px;
        text-align: right;
    }

    .ai-message .message-time {
        text-align: left;
    }

    .chat-input-container {
        padding: 15px 20px;
        border-top: 1px solid #e2e8f0;
        flex-shrink: 0;
        background: white;
        border-radius: 0 0 20px 20px;
    }

    .typing-indicator {
        display: none;
        margin-bottom: 15px;
    }

    .typing-content {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.8em;
        color: #64748b;
    }

    .typing-dots {
        display: flex;
        gap: 3px;
    }

    .typing-dots span {
        width: 6px;
        height: 6px;
        background: #64748b;
        border-radius: 50%;
        animation: typing 1.4s infinite ease-in-out;
    }

    .typing-dots span:nth-child(1) {
        animation-delay: -0.32s;
    }

    .typing-dots span:nth-child(2) {
        animation-delay: -0.16s;
    }

    @keyframes typing {

        0%,
        80%,
        100% {
            transform: scale(0);
            opacity: 0.5;
        }

        40% {
            transform: scale(1);
            opacity: 1;
        }
    }

    .action-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 15px;
    }

    .action-btn {
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 8px 16px;
        font-size: 0.8em;
        color: #475569;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .action-btn:hover {
        background: #e2e8f0;
        transform: translateY(-1px);
    }

    .input-group {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .btn-attachment {
        background: #f1f5f9;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: #64748b;
        transition: all 0.2s ease;
    }

    .btn-attachment:hover {
        background: #e2e8f0;
        color: #475569;
    }

    #chat-input {
        flex: 1;
        border: 1px solid #e2e8f0;
        border-radius: 25px;
        padding: 12px 20px;
        outline: none;
        font-size: 14px;
        background: #f8fafc;
        transition: all 0.2s ease;
    }

    #chat-input:focus {
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    #chat-send {
        border-radius: 50%;
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border: none;
        background: linear-gradient(135deg, #667eea, #764ba2);
        transition: all 0.2s ease;
    }

    #chat-send:hover {
        transform: scale(1.05);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    .quick-suggestions {
        margin-top: 15px;
    }

    .suggestion-title {
        font-size: 0.8em;
        color: #64748b;
        margin-bottom: 8px;
        font-weight: 600;
    }

    .suggestion-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .suggestion-chip {
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 6px 12px;
        font-size: 0.75em;
        color: #475569;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .suggestion-chip:hover {
        background: #e2e8f0;
        transform: translateY(-1px);
    }

    .chat-toggle-btn {
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 65px;
        height: 65px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border: none;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6em;
        cursor: pointer;
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        z-index: 9999;
        transition: all 0.3s ease;
        outline: none;
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-5px);
        }
    }

    .chat-toggle-btn:hover {
        transform: scale(1.1) translateY(-2px);
        box-shadow: 0 12px 30px rgba(102, 126, 234, 0.6);
    }

    .chat-notification {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #ef4444;
        color: white;
        border-radius: 50%;
        width: 22px;
        height: 22px;
        font-size: 0.7em;
        display: none;
        align-items: center;
        justify-content: center;
        animation: pulse 2s infinite;
        font-weight: bold;
    }

    .sample-project {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 15px;
        margin: 10px 0;
    }

    .sample-title {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 8px;
    }

    .sample-meta {
        display: flex;
        gap: 15px;
        font-size: 0.8em;
        color: #64748b;
        margin-top: 10px;
    }

    @media (max-width: 576px) {
        .chat-widget {
            width: calc(100vw - 30px);
            height: 70vh;
            right: 15px;
            left: 15px;
            bottom: 70px;
        }

        .chat-toggle-btn {
            bottom: 15px;
            right: 15px;
            width: 60px;
            height: 60px;
        }
    }
</style>

<script>
    // Enhanced chat widget JavaScript with advanced functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Get elements
        const chatWidget = document.getElementById('chat-widget');
        const chatToggle = document.getElementById('chat-toggle');
        const chatClose = document.getElementById('chat-close');
        const chatMinimize = document.getElementById('chat-minimize');
        const chatClear = document.getElementById('chat-clear');
        const chatMessages = document.getElementById('chat-messages');
        const chatInput = document.getElementById('chat-input');
        const chatSend = document.getElementById('chat-send');
        const chatAttachment = document.getElementById('chat-attachment');
        const typingIndicator = document.getElementById('typing-indicator');
        const chatNotification = document.getElementById('chat-notification');
        const actionButtons = document.getElementById('action-buttons');

        // State
        let isOpen = false;
        let isMinimized = false;
        let notificationCount = 0;
        let messageHistory = [];
        let sessionId = generateSessionId();
        let currentProjectType = '';

        // Initialize
        typingIndicator.style.display = 'none';
        chatNotification.style.display = 'none';

        // Generate unique session ID
        function generateSessionId() {
            return 'chat_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        }

        // Toggle chat
        chatToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (isMinimized) {
                chatWidget.classList.remove('minimized');
                isMinimized = false;
            }

            isOpen = !isOpen;
            chatWidget.classList.toggle('active', isOpen);

            if (isOpen) {
                clearNotifications();
                setTimeout(() => {
                    if (chatInput) chatInput.focus();
                }, 600);
            }
        });

        // Minimize chat
        chatMinimize.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            chatWidget.classList.add('minimized');
            isMinimized = true;
        });

        // Clear chat
        chatClear.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (confirm('Clear all chat messages?')) {
                const welcome = chatMessages.querySelector('.chat-welcome');
                chatMessages.innerHTML = '';
                if (welcome) {
                    chatMessages.appendChild(welcome);
                }
                messageHistory = [];
            }
        });

        // Close chat
        chatClose.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            closeChat();
        });

        // Close when clicking outside
        document.addEventListener('click', function(e) {
            if (isOpen && chatWidget && !chatWidget.contains(e.target) && e.target !== chatToggle) {
                closeChat();
            }
        });

        // Send message
        chatSend.addEventListener('click', sendMessage);

        // Send on Enter key
        chatInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        // Attachment button
        chatAttachment.addEventListener('click', function() {
            showFileUploadOptions();
        });

        // Quick actions
        document.querySelectorAll('.quick-action-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const action = this.getAttribute('data-action');
                handleQuickAction(action);
            });
        });

        // Suggestion chips
        document.querySelectorAll('.suggestion-chip').forEach(chip => {
            chip.addEventListener('click', function() {
                const message = this.getAttribute('data-message');
                if (message && chatInput) {
                    chatInput.value = message;
                    sendMessage();
                }
            });
        });

        function sendMessage() {
            const message = chatInput.value.trim();
            if (!message) return;

            // Add user message
            addMessage(message, 'user');
            chatInput.value = '';
            actionButtons.innerHTML = '';

            // Show typing indicator
            showTypingIndicator();

            // Send to server
            fetch('includes/chat_widget.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=send_message&message=${encodeURIComponent(message)}&session_id=${sessionId}`
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    hideTypingIndicator();
                    if (data.success) {
                        addMessage(data.response, 'ai');
                        if (data.actions && data.actions.length > 0) {
                            showActionButtons(data.actions);
                        }
                        // Store in history
                        messageHistory.push({
                            user: message,
                            ai: data.response,
                            timestamp: new Date().toISOString()
                        });
                    } else {
                        addMessage('❌ Sorry, there was an error. Please try again or call us directly at +256 771 200 234.', 'ai');
                    }
                })
                .catch(error => {
                    console.log('Chat error:', error);
                    hideTypingIndicator();
                    addMessage('📞 Thank you for your message! For immediate assistance, please call us directly at +256 771 200 234 or email Kbmocenter@gmail.com.', 'ai');
                });
        }

        function addMessage(text, type) {
            const messageDiv = document.createElement('div');
            messageDiv.className = type + '-message';

            const bubble = document.createElement('div');
            bubble.className = 'message-bubble';

            // Check if text contains sample project data
            if (text.includes('sample-project-data:')) {
                const projectData = JSON.parse(text.replace('sample-project-data:', ''));
                bubble.innerHTML = createSampleProjectHTML(projectData);
            } else {
                bubble.innerHTML = formatMessage(text);
            }

            const time = document.createElement('small');
            time.className = 'message-time';
            time.textContent = getCurrentTime();

            messageDiv.appendChild(bubble);
            messageDiv.appendChild(time);
            chatMessages.appendChild(messageDiv);

            // Scroll to bottom
            chatMessages.scrollTop = chatMessages.scrollHeight;

            // Show notification if chat is closed
            if (type === 'ai' && !isOpen) {
                showNotification();
            }
        }

        function formatMessage(text) {
            // Convert line breaks and format text
            return text.replace(/\n/g, '<br>')
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>');
        }

        function createSampleProjectHTML(project) {
            return `
                <div class="sample-project">
                    <div class="sample-title">${project.title}</div>
                    <div class="sample-description">${project.description}</div>
                    <div class="sample-meta">
                        <span>📄 ${project.pages}</span>
                        <span>🔬 ${project.methodology}</span>
                        <span>⏱️ ${project.completion}</span>
                    </div>
                </div>
                <p>Interested in this type of research? Let me know if you'd like to:</p>
                <div style="margin-top: 10px;">
                    <button class="action-btn" onclick="startSimilarProject('${project.title}')">
                        🚀 Start Similar Project
                    </button>
                    <button class="action-btn" onclick="requestCustomQuote()">
                        💰 Get Custom Quote
                    </button>
                </div>
            `;
        }

        function showActionButtons(actions) {
            actionButtons.innerHTML = '';
            actions.forEach(action => {
                const button = document.createElement('button');
                button.className = 'action-btn';
                button.innerHTML = action.text;
                button.onclick = () => handleAction(action.value);
                actionButtons.appendChild(button);
            });
        }

        function handleAction(action) {
            switch (action) {
                case 'services':
                    chatInput.value = 'What research services do you offer?';
                    sendMessage();
                    break;
                case 'start_project':
                    startProjectWizard();
                    break;
                case 'view_samples':
                    showSampleCategories();
                    break;
                case 'upload_documents':
                    showUploadOptions();
                    break;
                case 'get_quote':
                    startQuoteProcess();
                    break;
                case 'create_account':
                    showRegistrationForm();
                    break;
                default:
                    chatInput.value = action;
                    sendMessage();
            }
        }

        function handleQuickAction(action) {
            switch (action) {
                case 'start_project':
                    startProjectWizard();
                    break;
                case 'view_samples':
                    showSampleCategories();
                    break;
                case 'get_pricing':
                    chatInput.value = 'What are your prices for research services?';
                    sendMessage();
                    break;
                case 'talk_expert':
                    addMessage('👨‍💼 Connecting you with a research expert...', 'ai');
                    setTimeout(() => {
                        addMessage('✅ Our expert will contact you within 5 minutes. In the meantime, please call +256 771 200 234 for immediate assistance.', 'ai');
                    }, 2000);
                    break;
            }
        }

        function startProjectWizard() {
            const steps = [
                "🎯 What type of research project are you working on? (Thesis, Proposal, Data Analysis, etc.)",
                "📚 What is your field of study?",
                "⏰ What is your deadline?",
                "📄 Do you have any existing materials?"
            ];

            let currentStep = 0;

            function askNextQuestion() {
                if (currentStep < steps.length) {
                    addMessage(steps[currentStep], 'ai');
                    currentStep++;
                } else {
                    addMessage("🚀 Perfect! I have all the basic information. Let me connect you with a specialist who can provide a detailed project plan and quote.", 'ai');
                    showActionButtons([{
                            text: '📞 Call Specialist',
                            value: 'call_specialist'
                        },
                        {
                            text: '📧 Email Details',
                            value: 'email_details'
                        },
                        {
                            text: '💬 Continue Chat',
                            value: 'continue_chat'
                        }
                    ]);
                }
            }

            askNextQuestion();
        }

        function showSampleCategories() {
            addMessage("📚 Here are our research sample categories:", 'ai');
            showActionButtons([{
                    text: '🏥 Health Research',
                    value: 'health_samples'
                },
                {
                    text: '📚 Education Studies',
                    value: 'education_samples'
                },
                {
                    text: '💼 Business Research',
                    value: 'business_samples'
                },
                {
                    text: '🌿 Environmental Studies',
                    value: 'environment_samples'
                }
            ]);
        }

        function showUploadOptions() {
            addMessage("📤 You can upload your research materials through:", 'ai');
            showActionButtons([{
                    text: '🔒 Secure Portal',
                    value: 'secure_upload'
                },
                {
                    text: '📧 Email Attachment',
                    value: 'email_upload'
                },
                {
                    text: '📱 Mobile Upload',
                    value: 'mobile_upload'
                }
            ]);
        }

        function showFileUploadOptions() {
            addMessage("📎 You can upload:", 'ai');
            showActionButtons([{
                    text: '📄 Research Proposal Draft',
                    value: 'upload_proposal'
                },
                {
                    text: '📊 Data Files',
                    value: 'upload_data'
                },
                {
                    text: '📑 Literature Materials',
                    value: 'upload_literature'
                },
                {
                    text: '📝 Thesis Chapters',
                    value: 'upload_thesis'
                }
            ]);
        }

        function showRegistrationForm() {
            addMessage("👤 Let's create your research account. Please provide:", 'ai');
            // In a real implementation, this would show a form
            setTimeout(() => {
                addMessage("✅ Account creation initiated! Our team will contact you to complete registration and discuss your research needs.", 'ai');
            }, 1500);
        }

        function showTypingIndicator() {
            typingIndicator.style.display = 'block';
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function hideTypingIndicator() {
            typingIndicator.style.display = 'none';
        }

        function getCurrentTime() {
            const now = new Date();
            return now.getHours().toString().padStart(2, '0') + ':' +
                now.getMinutes().toString().padStart(2, '0');
        }

        function showNotification() {
            notificationCount++;
            chatNotification.textContent = notificationCount > 9 ? '9+' : notificationCount.toString();
            chatNotification.style.display = 'flex';

            // Add pulse animation
            chatNotification.style.animation = 'pulse 2s infinite';
        }

        function clearNotifications() {
            notificationCount = 0;
            chatNotification.style.display = 'none';
        }

        function closeChat() {
            isOpen = false;
            isMinimized = false;
            chatWidget.classList.remove('active', 'minimized');

            // Notify server
            fetch('includes/chat_widget.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=close_chat'
            });
        }

        // Global functions for sample project actions
        window.startSimilarProject = function(title) {
            addMessage(`🚀 I'd like to start a project similar to: ${title}`, 'user');
            setTimeout(() => {
                startProjectWizard();
            }, 1000);
        };

        window.requestCustomQuote = function() {
            addMessage('💰 I need a custom quote for my research project', 'user');
            setTimeout(() => {
                startQuoteProcess();
            }, 1000);
        };

        function startQuoteProcess() {
            addMessage("💵 Let me gather some information for your custom quote:", 'ai');
            setTimeout(() => {
                startProjectWizard();
            }, 1500);
        }

        // Auto-show welcome notification after 20 seconds
        setTimeout(() => {
            if (!isOpen && notificationCount === 0) {
                showNotification();
            }
        }, 20000);

        // Enhanced error handling
        window.addEventListener('online', function() {
            if (chatMessages.querySelectorAll('.user-message').length >
                chatMessages.querySelectorAll('.ai-message').length) {
                addMessage('✅ Connection restored! You can continue chatting.', 'ai');
            }
        });

        window.addEventListener('offline', function() {
            addMessage('⚠️ You appear to be offline. Some features may not work.', 'ai');
        });

        // Auto-focus when opening
        chatWidget.addEventListener('transitionend', function() {
            if (isOpen && !isMinimized) {
                chatInput.focus();
            }
        });
    });
</script>