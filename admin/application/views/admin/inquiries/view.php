<?php
$attachments_by_reply = isset($attachments_by_reply) ? $attachments_by_reply : array();
$can_delete = !empty($can_delete);
$can_edit = !empty($can_edit);

$badge = 'secondary';
if ($inquiry->status === 'new') $badge = 'danger';
elseif ($inquiry->status === 'guest_replied') $badge = 'primary';
elseif ($inquiry->status === 'read') $badge = 'warning';
elseif ($inquiry->status === 'replied') $badge = 'success';
elseif ($inquiry->status === 'closed') $badge = 'info';
?>
<style>
    .inquiry-reply-body p { margin: 0 0 10px; }
    .inquiry-reply-body p:last-child { margin-bottom: 0; }
    .inquiry-reply-body ul,
    .inquiry-reply-body ol { margin: 0 0 10px 18px; padding: 0; }
    .inquiry-attachments { margin-top: 12px; padding-top: 10px; border-top: 1px dashed #d7dde8; }
    .inquiry-attachments ul { margin: 0; padding: 0; list-style: none; }
    .inquiry-attachments li { margin: 0 0 6px; }
    .inquiry-attachment-hint { color: #8091a7; font-size: 12px; margin-top: 6px; }
</style>
<div class="content-card">
    <div class="d-flex flex-wrap justify-content-between align-items-start mb-3 gap-2">
        <div>
            <h5 class="mb-1"><i class="bi bi-envelope-open"></i> Inquiry #<?php echo (int) $inquiry->inquiryid; ?></h5>
            <p class="mb-0">
                <span class="badge bg-<?php echo $badge; ?>"><?php echo ucwords(str_replace('_', ' ', $inquiry->status)); ?></span>
                <span class="text-muted ms-2"><?php echo !empty($inquiry->created_at) ? date('F j, Y g:i A', strtotime($inquiry->created_at)) : $inquiry->cdate; ?></span>
            </p>
        </div>
        <a href="<?php echo base_url('inquiries'); ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</a>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $this->session->flashdata('success'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $this->session->flashdata('error'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row mb-3">
        <div class="col-md-4 mb-2">
            <div class="text-muted small">Name</div>
            <strong><?php echo htmlspecialchars($inquiry->name, ENT_QUOTES, 'UTF-8'); ?></strong>
        </div>
        <div class="col-md-4 mb-2">
            <div class="text-muted small">Email</div>
            <strong><a href="mailto:<?php echo htmlspecialchars($inquiry->email, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($inquiry->email, ENT_QUOTES, 'UTF-8'); ?></a></strong>
        </div>
        <div class="col-md-4 mb-2">
            <div class="text-muted small">Subject</div>
            <strong><?php echo htmlspecialchars($inquiry->subject, ENT_QUOTES, 'UTF-8'); ?></strong>
        </div>
    </div>

    <div class="p-3 mb-4 rounded" style="background:#f7f9fc;">
        <h6 class="mb-2">Message</h6>
        <p class="mb-0" style="white-space:pre-wrap;"><?php echo htmlspecialchars($inquiry->message, ENT_QUOTES, 'UTF-8'); ?></p>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-4 align-items-center">
        <?php if ($can_edit): ?>
        <form action="<?php echo base_url('inquiries/updatestatus'); ?>" method="post" class="d-flex flex-wrap gap-2 align-items-center">
            <input type="hidden" name="inquiryid" value="<?php echo (int) $inquiry->inquiryid; ?>">
            <label class="mb-0">Status:</label>
            <select name="status" class="form-select form-select-sm" style="width:auto;">
                <option value="new" <?php echo $inquiry->status === 'new' ? 'selected' : ''; ?>>New</option>
                <option value="guest_replied" <?php echo $inquiry->status === 'guest_replied' ? 'selected' : ''; ?>>Guest Replied</option>
                <option value="read" <?php echo $inquiry->status === 'read' ? 'selected' : ''; ?>>Read</option>
                <option value="replied" <?php echo $inquiry->status === 'replied' ? 'selected' : ''; ?>>Replied</option>
                <option value="closed" <?php echo $inquiry->status === 'closed' ? 'selected' : ''; ?>>Closed</option>
            </select>
            <button type="submit" class="btn btn-sm btn-warning">Update</button>
        </form>
        <form action="<?php echo base_url('inquiries/fetchinbound'); ?>" method="post">
            <input type="hidden" name="redirect" value="inquiries/<?php echo (int) $inquiry->inquiryid; ?>">
            <button type="submit" class="btn btn-sm btn-info"><i class="bi bi-envelope"></i> Check Email Replies</button>
        </form>
        <?php endif; ?>
        <?php if ($can_delete): ?>
        <a href="<?php echo base_url('inquiries/delete/' . (int) $inquiry->inquiryid); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this inquiry permanently?');"><i class="bi bi-trash"></i> Delete</a>
        <?php endif; ?>
    </div>

    <hr>
    <h5 class="mb-3"><i class="bi bi-chat-dots"></i> Conversation</h5>

    <?php if (empty($replies)) { ?>
        <p class="text-muted">No replies yet.</p>
    <?php } else { ?>
        <?php foreach ($replies as $reply) {
            $isInbound = isset($reply->direction) && $reply->direction === 'inbound';
            if ($isInbound) {
                $displayName = trim($reply->sender_name ? $reply->sender_name : $inquiry->name);
                $bubbleClass = 'border-success bg-light';
            } else {
                $displayName = !empty($reply->admin_name) ? $reply->admin_name : 'Staff';
                $bubbleClass = 'border-secondary bg-white';
            }
            ?>
            <div class="border rounded p-3 mb-3 <?php echo $bubbleClass; ?>">
                <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                    <strong><?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></strong>
                    <span class="text-muted small">
                        <?php echo !empty($reply->created_at) ? date('M j, Y g:i A', strtotime($reply->created_at)) : $reply->cdate; ?>
                        <?php if ($isInbound) { ?>
                            <span class="badge bg-info">Received via Email</span>
                        <?php } elseif (!(int) $reply->email_sent) { ?>
                            <span class="badge bg-warning text-dark">Email Failed</span>
                        <?php } else { ?>
                            <span class="badge bg-success">Email Sent</span>
                        <?php } ?>
                    </span>
                </div>
                <p class="mb-2"><strong>Subject:</strong> <?php echo htmlspecialchars($reply->reply_subject, ENT_QUOTES, 'UTF-8'); ?></p>
                <div class="inquiry-reply-body"><?php echo format_inquiry_reply_body($reply->reply_message, $isInbound); ?></div>
                <?php
                $replyAttachments = !empty($attachments_by_reply[(int) $reply->replyid]) ? $attachments_by_reply[(int) $reply->replyid] : array();
                if (!empty($replyAttachments)) { ?>
                    <div class="inquiry-attachments">
                        <strong>Attachments:</strong>
                        <ul>
                            <?php foreach ($replyAttachments as $attachment) { ?>
                                <li>
                                    <a href="<?php echo base_url('inquiries/downloadattachment/' . (int) $attachment->attachmentid); ?>">
                                        <i class="bi bi-paperclip"></i>
                                        <?php echo htmlspecialchars($attachment->original_filename, ENT_QUOTES, 'UTF-8'); ?>
                                        <span class="text-muted">(<?php echo format_inquiry_file_size($attachment->file_size); ?>)</span>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    <?php } ?>

    <?php if ($can_edit): ?>
    <hr>
    <h5 class="mb-2"><i class="bi bi-send"></i> Send Reply</h5>
    <p class="text-muted small">Reply will be emailed to <strong><?php echo htmlspecialchars($inquiry->email, ENT_QUOTES, 'UTF-8'); ?></strong></p>

    <form action="<?php echo base_url('inquiries/reply'); ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="inquiryid" value="<?php echo (int) $inquiry->inquiryid; ?>">
        <div class="mb-3">
            <label class="form-label">Subject</label>
            <input type="text" class="form-control" name="reply_subject" value="Re: <?php echo htmlspecialchars($inquiry->subject, ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Message</label>
            <textarea class="form-control" name="reply_message" rows="8" required placeholder="Type your reply..."></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Attachments</label>
            <input type="file" class="form-control" name="attachments[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,.jpg,.jpeg,.png">
            <p class="inquiry-attachment-hint">Up to 5 files, 10 MB each, 20 MB total. Allowed: PDF, Word, Excel, TXT, JPG, PNG.</p>
        </div>
        <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Send Reply</button>
    </form>
    <?php endif; ?>
</div>
