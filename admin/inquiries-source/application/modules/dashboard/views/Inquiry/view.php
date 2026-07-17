<?php
$attachments_by_reply = isset($attachments_by_reply) ? $attachments_by_reply : array();
$statusStyle = 'background:#8091a7;color:#fff;padding:3px 8px;border-radius:3px;font-size:12px;';
if ($inquiry->status === 'new') {
    $statusStyle = 'background:#e85347;color:#fff;padding:3px 8px;border-radius:3px;font-size:12px;';
} elseif ($inquiry->status === 'guest_replied') {
    $statusStyle = 'background:#7e57c2;color:#fff;padding:3px 8px;border-radius:3px;font-size:12px;';
} elseif ($inquiry->status === 'read') {
    $statusStyle = 'background:#f4bd0e;color:#1f2b3a;padding:3px 8px;border-radius:3px;font-size:12px;';
} elseif ($inquiry->status === 'replied') {
    $statusStyle = 'background:#1ee0ac;color:#1f2b3a;padding:3px 8px;border-radius:3px;font-size:12px;';
} elseif ($inquiry->status === 'closed') {
    $statusStyle = 'background:#09c2de;color:#fff;padding:3px 8px;border-radius:3px;font-size:12px;';
}
?>
<style>
    .inquiry-reply-body p { margin: 0 0 10px; }
    .inquiry-reply-body p:last-child { margin-bottom: 0; }
    .inquiry-reply-body ul,
    .inquiry-reply-body ol { margin: 0 0 10px 18px; padding: 0; }
    .inquiry-reply-body a { color: #36661f; text-decoration: underline; }
    .inquiry-attachments { margin-top: 12px; padding-top: 10px; border-top: 1px dashed #d7dde8; }
    .inquiry-attachments ul { margin: 0; padding: 0; list-style: none; }
    .inquiry-attachments li { margin: 0 0 6px; }
    .inquiry-attachments a { display: inline-flex; align-items: center; gap: 6px; color: #36661f; text-decoration: none; }
    .inquiry-attachments a:hover { text-decoration: underline; }
    .inquiry-attachment-hint { color: #8091a7; font-size: 12px; margin-top: 6px; }
</style>
<div class="content view_event">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-offset-1 col-md-10">
                <div class="card card-product">
                    <div class="card-header" data-background-color="purple">
                        <h4 class="title"><i class="material-icons">mail_outline</i> <?php echo $this->lang->line('dash_viewinquiry_panel_title'); ?> #<?php echo (int) $inquiry->inquiryid; ?></h4>
                        <p class="category">
                            <span style="<?php echo $statusStyle; ?>"><?php echo ucwords(str_replace('_', ' ', $inquiry->status)); ?></span>
                            &nbsp; <?php echo !empty($inquiry->created_at) ? date('F j, Y g:i A', strtotime($inquiry->created_at)) : $inquiry->cdate; ?>
                        </p>
                    </div>
                    <div class="card-content">
                        <div class="row" style="margin-bottom:20px;">
                            <div class="col-md-4">
                                <p class="category text-gray"><i class="material-icons">person</i> <?php echo $this->lang->line('dash_gpanel_name'); ?></p>
                                <h5><?php echo htmlspecialchars($inquiry->name, ENT_QUOTES, 'UTF-8'); ?></h5>
                            </div>
                            <div class="col-md-4">
                                <p class="category text-gray"><i class="material-icons">email</i> <?php echo $this->lang->line('dash_gpanel_email'); ?></p>
                                <h5><a href="mailto:<?php echo htmlspecialchars($inquiry->email, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($inquiry->email, ENT_QUOTES, 'UTF-8'); ?></a></h5>
                            </div>
                            <div class="col-md-4">
                                <p class="category text-gray"><i class="material-icons">subject</i> <?php echo $this->lang->line('dash_gpanel_subject'); ?></p>
                                <h5><?php echo htmlspecialchars($inquiry->subject, ENT_QUOTES, 'UTF-8'); ?></h5>
                            </div>
                        </div>

                        <div class="card-description" style="background:#f7f9fc;padding:18px;border-radius:4px;margin-bottom:25px;">
                            <h5 style="margin-top:0;"><?php echo $this->lang->line('dash_gpanel_message'); ?></h5>
                            <p style="white-space:pre-wrap;margin-bottom:0;"><?php echo htmlspecialchars($inquiry->message, ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>

                        <div class="row" style="margin-bottom:25px;">
                            <div class="col-md-12">
                                <form action="<?php echo base_url('dashboard/inquiry/updatestatus'); ?>" method="post" class="form-inline" style="display:inline-block;margin-right:8px;">
                                    <input type="hidden" name="inquiryid" value="<?php echo (int) $inquiry->inquiryid; ?>">
                                    <label style="margin-right:10px;"><?php echo $this->lang->line('dash_gpanel_status'); ?>:</label>
                                    <select name="status" class="form-control" style="margin-right:10px;">
                                        <option value="new" <?php echo $inquiry->status === 'new' ? 'selected' : ''; ?>><?php echo $this->lang->line('dash_gpanel_status_new'); ?></option>
                                        <option value="guest_replied" <?php echo $inquiry->status === 'guest_replied' ? 'selected' : ''; ?>><?php echo $this->lang->line('dash_gpanel_status_guest_replied'); ?></option>
                                        <option value="read" <?php echo $inquiry->status === 'read' ? 'selected' : ''; ?>><?php echo $this->lang->line('dash_gpanel_status_read'); ?></option>
                                        <option value="replied" <?php echo $inquiry->status === 'replied' ? 'selected' : ''; ?>><?php echo $this->lang->line('dash_gpanel_status_replied'); ?></option>
                                        <option value="closed" <?php echo $inquiry->status === 'closed' ? 'selected' : ''; ?>><?php echo $this->lang->line('dash_gpanel_status_closed'); ?></option>
                                    </select>
                                    <button type="submit" class="btn btn-warning btn-sm"><i class="material-icons">sync</i> <?php echo $this->lang->line('dash_gpanel_update'); ?></button>
                                </form>
                                <form action="<?php echo base_url('dashboard/inquiry/fetchinbound'); ?>" method="post" style="display:inline-block;margin-right:8px;">
                                    <input type="hidden" name="redirect" value="dashboard/inquiry/view/<?php echo (int) $inquiry->inquiryid; ?>">
                                    <button type="submit" class="btn btn-info btn-sm"><i class="material-icons">email</i> <?php echo $this->lang->line('dash_gpanel_fetch_email_replies'); ?></button>
                                </form>
                                <a href="<?php echo base_url('dashboard/inquiry/allinquiries'); ?>" class="btn btn-default btn-sm"><i class="material-icons">arrow_back</i> <?php echo $this->lang->line('dash_gpanel_back'); ?></a>
                                <a href="<?php echo base_url('dashboard/inquiry/delete/' . $inquiry->inquiryid); ?>" class="btn btn-danger btn-sm delete"><i class="material-icons">clear</i> <?php echo $this->lang->line('dash_gpanel_delete'); ?></a>
                            </div>
                        </div>

                        <hr />

                        <h4><i class="material-icons">forum</i> <?php echo $this->lang->line('dash_gpanel_conversation'); ?></h4>

                        <?php if (empty($replies)) { ?>
                            <p class="text-muted"><?php echo $this->lang->line('dash_gpanel_noreplyyet'); ?></p>
                        <?php } else { ?>
                            <?php foreach ($replies as $reply) {
                                $isInbound = isset($reply->direction) && $reply->direction === 'inbound';
                                if ($isInbound) {
                                    $displayName = trim($reply->sender_name ? $reply->sender_name : $inquiry->name);
                                    $bubbleStyle = 'border:1px solid #c8e6c9;border-radius:4px;padding:15px;margin-bottom:15px;background:#f1f8f4;';
                                    $icon = 'mail';
                                } else {
                                    $displayName = trim(($reply->fname ? $reply->fname : '') . ' ' . ($reply->lname ? $reply->lname : ''));
                                    if ($displayName === '') {
                                        $displayName = 'Staff';
                                    }
                                    $bubbleStyle = 'border:1px solid #e3e8ef;border-radius:4px;padding:15px;margin-bottom:15px;background:#fff;';
                                    $icon = 'reply';
                                }
                                ?>
                                <div style="<?php echo $bubbleStyle; ?>">
                                    <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                                        <strong><i class="material-icons" style="font-size:16px;vertical-align:middle;"><?php echo $icon; ?></i> <?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></strong>
                                        <span class="text-muted">
                                            <?php echo !empty($reply->created_at) ? date('M j, Y g:i A', strtotime($reply->created_at)) : $reply->cdate; ?>
                                            <?php if ($isInbound) { ?>
                                                <span style="background:#09c2de;color:#fff;padding:2px 6px;border-radius:3px;font-size:11px;"><?php echo $this->lang->line('dash_gpanel_received_via_email'); ?></span>
                                            <?php } elseif (!(int) $reply->email_sent) { ?>
                                                <span style="background:#f4bd0e;color:#1f2b3a;padding:2px 6px;border-radius:3px;font-size:11px;"><?php echo $this->lang->line('dash_gpanel_emailsendfailed'); ?></span>
                                            <?php } else { ?>
                                                <span style="background:#1ee0ac;color:#1f2b3a;padding:2px 6px;border-radius:3px;font-size:11px;"><?php echo $this->lang->line('dash_gpanel_emailsent'); ?></span>
                                            <?php } ?>
                                        </span>
                                    </div>
                                    <p style="margin-bottom:6px;"><strong><?php echo $this->lang->line('dash_gpanel_subject'); ?>:</strong> <?php echo htmlspecialchars($reply->reply_subject, ENT_QUOTES, 'UTF-8'); ?></p>
                                    <div class="inquiry-reply-body"><?php echo format_inquiry_reply_body($reply->reply_message, $isInbound); ?></div>
                                    <?php
                                    $replyAttachments = !empty($attachments_by_reply[(int) $reply->replyid]) ? $attachments_by_reply[(int) $reply->replyid] : array();
                                    if (!empty($replyAttachments)) { ?>
                                        <div class="inquiry-attachments">
                                            <strong><?php echo $this->lang->line('dash_gpanel_attachments'); ?>:</strong>
                                            <ul>
                                                <?php foreach ($replyAttachments as $attachment) { ?>
                                                    <li>
                                                        <a href="<?php echo base_url('dashboard/inquiry/downloadattachment/' . (int) $attachment->attachmentid); ?>">
                                                            <i class="material-icons" style="font-size:16px;">attach_file</i>
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

                        <hr />

                        <h4><i class="material-icons">send</i> <?php echo $this->lang->line('dash_gpanel_sendreply'); ?></h4>
                        <p class="category text-gray"><?php echo $this->lang->line('dash_gpanel_replyhint'); ?> <strong><?php echo htmlspecialchars($inquiry->email, ENT_QUOTES, 'UTF-8'); ?></strong></p>

                        <form action="<?php echo base_url('dashboard/inquiry/reply'); ?>" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="inquiryid" value="<?php echo (int) $inquiry->inquiryid; ?>">
                            <div class="form-group label-floating">
                                <label class="control-label"><?php echo $this->lang->line('dash_gpanel_subject'); ?></label>
                                <input type="text" class="form-control" name="reply_subject" value="Re: <?php echo htmlspecialchars($inquiry->subject, ENT_QUOTES, 'UTF-8'); ?>" required>
                            </div>
                            <div class="form-group label-floating">
                                <label class="control-label"><?php echo $this->lang->line('dash_gpanel_replymessage'); ?></label>
                                <textarea class="form-control inquiry-reply-editor" name="reply_message" rows="8" required placeholder="<?php echo $this->lang->line('dash_gpanel_replyplaceholder'); ?>"></textarea>
                            </div>
                            <div class="form-group">
                                <label class="control-label"><?php echo $this->lang->line('dash_gpanel_attachments'); ?></label>
                                <input type="file" class="form-control" name="attachments[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,.jpg,.jpeg,.png">
                                <p class="inquiry-attachment-hint"><?php echo $this->lang->line('dash_gpanel_attachment_hint'); ?></p>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="material-icons">send</i> <?php echo $this->lang->line('dash_gpanel_sendreply'); ?></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
