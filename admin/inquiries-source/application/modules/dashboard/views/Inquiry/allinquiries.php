<?php
$filter_status = isset($filter_status) ? $filter_status : '';
$filter_range = isset($filter_range) ? $filter_range : 'today';
$filter_date_from = isset($filter_date_from) ? $filter_date_from : date('Y-m-d');
$filter_date_to = isset($filter_date_to) ? $filter_date_to : date('Y-m-d');
$date_query = isset($date_query) ? $date_query : ('range=today&date_from=' . date('Y-m-d') . '&date_to=' . date('Y-m-d'));

$statusLinkBase = base_url('dashboard/inquiry/allinquiries');
$buildStatusUrl = function ($status = '') use ($statusLinkBase, $date_query) {
    $params = array();
    if ($status !== '') {
        $params[] = 'status=' . urlencode($status);
    }
    if ($date_query !== '') {
        $params[] = $date_query;
    }
    return $statusLinkBase . (!empty($params) ? ('?' . implode('&', $params)) : '');
};

$fetchRedirect = 'dashboard/inquiry/allinquiries';
$fetchParams = array();
if ($filter_status) {
    $fetchParams[] = 'status=' . urlencode($filter_status);
}
if ($date_query) {
    $fetchParams[] = $date_query;
}
if (!empty($fetchParams)) {
    $fetchRedirect .= '?' . implode('&', $fetchParams);
}
?>
<div class="content gusers">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card">
                    <div class="card-header" data-background-color="purple">
                        <h4 class="title"><i class="material-icons">mail</i> <?php echo $this->lang->line('dash_allinquiry_panel_title'); ?> ( <?php echo (int) $counts['all']; ?> )</h4>
                        <p class="category"><?php echo $this->lang->line('dash_gpanel_newinquiry'); ?> <?php echo getCreateDate('inquiryid', 'inquiry'); ?></p>
                    </div>
                    <div class="card-content table-responsive">
                        <div class="row" style="margin-bottom:15px;">
                            <div class="col-md-12">
                                <form action="<?php echo base_url('dashboard/inquiry/fetchinbound'); ?>" method="post" style="display:inline-block;margin-right:8px;">
                                    <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($fetchRedirect, ENT_QUOTES, 'UTF-8'); ?>">
                                    <button type="submit" class="btn btn-sm btn-info">
                                        <i class="material-icons" style="font-size:16px;vertical-align:middle;">sync</i>
                                        <?php echo $this->lang->line('dash_gpanel_fetch_email_replies'); ?>
                                    </button>
                                </form>
                                <a href="<?php echo $buildStatusUrl(''); ?>" class="btn btn-sm <?php echo empty($filter_status) ? 'btn-primary' : 'btn-default'; ?>">
                                    <?php echo $this->lang->line('dash_gpanel_all'); ?> (<?php echo (int) $counts['all']; ?>)
                                </a>
                                <a href="<?php echo $buildStatusUrl('new'); ?>" class="btn btn-sm <?php echo $filter_status === 'new' ? 'btn-primary' : 'btn-default'; ?>">
                                    <?php echo $this->lang->line('dash_gpanel_status_new'); ?> (<?php echo (int) $counts['new']; ?>)
                                </a>
                                <a href="<?php echo $buildStatusUrl('guest_replied'); ?>" class="btn btn-sm <?php echo $filter_status === 'guest_replied' ? 'btn-primary' : 'btn-default'; ?>">
                                    <?php echo $this->lang->line('dash_gpanel_status_guest_replied'); ?> (<?php echo (int) $counts['guest_replied']; ?>)
                                </a>
                                <a href="<?php echo $buildStatusUrl('read'); ?>" class="btn btn-sm <?php echo $filter_status === 'read' ? 'btn-primary' : 'btn-default'; ?>">
                                    <?php echo $this->lang->line('dash_gpanel_status_read'); ?> (<?php echo (int) $counts['read']; ?>)
                                </a>
                                <a href="<?php echo $buildStatusUrl('replied'); ?>" class="btn btn-sm <?php echo $filter_status === 'replied' ? 'btn-primary' : 'btn-default'; ?>">
                                    <?php echo $this->lang->line('dash_gpanel_status_replied'); ?> (<?php echo (int) $counts['replied']; ?>)
                                </a>
                                <a href="<?php echo $buildStatusUrl('closed'); ?>" class="btn btn-sm <?php echo $filter_status === 'closed' ? 'btn-primary' : 'btn-default'; ?>">
                                    <?php echo $this->lang->line('dash_gpanel_status_closed'); ?> (<?php echo (int) $counts['closed']; ?>)
                                </a>
                            </div>
                        </div>

                        <style>
                            .dtInquiry tbody tr.inquiry-row-link { cursor: pointer; }
                            .dtInquiry tbody tr.inquiry-row-link:hover td { background: #f7f9fc; }
                            .inquiry-date-filter {
                                display: inline-flex;
                                flex-wrap: wrap;
                                align-items: center;
                                gap: 6px;
                                margin: 0;
                            }
                            .inquiry-date-filter select,
                            .inquiry-date-filter input[type="date"] {
                                height: 34px;
                                padding: 4px 8px;
                                border: 1px solid #d7dde8;
                                border-radius: 4px;
                                background: #fff;
                                font-size: 13px;
                            }
                            .inquiry-date-filter .inquiry-date-inputs {
                                display: inline-flex;
                                align-items: center;
                                gap: 6px;
                            }
                            .inquiry-date-filter .inquiry-date-inputs.is-locked input {
                                background: #f5f7fb;
                                color: #6b7785;
                            }
                            .inquiry-date-filter .btn {
                                margin: 0;
                            }
                            .dataTables_wrapper .inquiry-dt-date {
                                text-align: center;
                                padding-top: 4px;
                            }
                            @media (max-width: 991px) {
                                .dataTables_wrapper .inquiry-dt-date {
                                    text-align: left;
                                    margin: 8px 0;
                                }
                            }
                        </style>

                        <form id="inquiry-date-filter" class="inquiry-date-filter" method="get" action="<?php echo base_url('dashboard/inquiry/allinquiries'); ?>" style="display:none;">
                            <?php if ($filter_status) { ?>
                                <input type="hidden" name="status" value="<?php echo htmlspecialchars($filter_status, ENT_QUOTES, 'UTF-8'); ?>">
                            <?php } ?>
                            <select name="range" id="inquiry-range" aria-label="<?php echo $this->lang->line('dash_gpanel_date_range'); ?>">
                                <option value="today" <?php echo $filter_range === 'today' ? 'selected' : ''; ?>><?php echo $this->lang->line('dash_gpanel_today'); ?></option>
                                <option value="7" <?php echo $filter_range === '7' ? 'selected' : ''; ?>><?php echo $this->lang->line('dash_gpanel_last_7_days'); ?></option>
                                <option value="30" <?php echo $filter_range === '30' ? 'selected' : ''; ?>><?php echo $this->lang->line('dash_gpanel_last_30_days'); ?></option>
                                <option value="custom" <?php echo $filter_range === 'custom' ? 'selected' : ''; ?>><?php echo $this->lang->line('dash_gpanel_custom_range'); ?></option>
                            </select>
                            <span class="inquiry-date-inputs <?php echo $filter_range !== 'custom' ? 'is-locked' : ''; ?>" id="inquiry-date-inputs">
                                <input type="date" name="date_from" id="inquiry-date-from" value="<?php echo htmlspecialchars($filter_date_from, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $filter_range !== 'custom' ? 'readonly' : ''; ?>>
                                <span><?php echo $this->lang->line('dash_gpanel_to'); ?></span>
                                <input type="date" name="date_to" id="inquiry-date-to" value="<?php echo htmlspecialchars($filter_date_to, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $filter_range !== 'custom' ? 'readonly' : ''; ?>>
                            </span>
                            <button type="submit" class="btn btn-sm btn-primary"><?php echo $this->lang->line('dash_gpanel_filter'); ?></button>
                        </form>

                        <table class="dtInquiry table table-hover">
                            <thead class="text-default">
                                <th><?php echo $this->lang->line('dash_gpanel_no'); ?></th>
                                <th><?php echo $this->lang->line('dash_gpanel_name'); ?></th>
                                <th><?php echo $this->lang->line('dash_gpanel_email'); ?></th>
                                <th><?php echo $this->lang->line('dash_gpanel_subject'); ?></th>
                                <th><?php echo $this->lang->line('dash_gpanel_status'); ?></th>
                                <th><?php echo $this->lang->line('dash_gpanel_date'); ?></th>
                                <th><?php echo $this->lang->line('dash_gpanel_action'); ?></th>
                            </thead>
                            <tbody>
                                <?php
                                $i = 0;
                                foreach ($inquiries as $row) {
                                    $i++;
                                    $statusStyle = 'background:#8091a7;color:#fff;padding:3px 8px;border-radius:3px;font-size:12px;';
                                    if ($row->status === 'new') {
                                        $statusStyle = 'background:#e85347;color:#fff;padding:3px 8px;border-radius:3px;font-size:12px;';
                                    } elseif ($row->status === 'guest_replied') {
                                        $statusStyle = 'background:#7e57c2;color:#fff;padding:3px 8px;border-radius:3px;font-size:12px;';
                                    } elseif ($row->status === 'read') {
                                        $statusStyle = 'background:#f4bd0e;color:#1f2b3a;padding:3px 8px;border-radius:3px;font-size:12px;';
                                    } elseif ($row->status === 'replied') {
                                        $statusStyle = 'background:#1ee0ac;color:#1f2b3a;padding:3px 8px;border-radius:3px;font-size:12px;';
                                    } elseif ($row->status === 'closed') {
                                        $statusStyle = 'background:#09c2de;color:#fff;padding:3px 8px;border-radius:3px;font-size:12px;';
                                    }
                                    ?>
                                    <tr class="inquiry-row-link" data-href="<?php echo base_url('dashboard/inquiry/view/' . (int) $row->inquiryid); ?>"<?php echo in_array($row->status, array('new', 'guest_replied'), TRUE) ? ' style="font-weight:600;"' : ''; ?>>
                                        <td><?php echo $i; ?></td>
                                        <td><?php echo htmlspecialchars($row->name, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($row->email, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars(character_limiter($row->subject, 40), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><span style="<?php echo $statusStyle; ?>"><?php echo ucwords(str_replace('_', ' ', $row->status)); ?></span></td>
                                        <td><?php echo !empty($row->created_at) ? date('M j, Y g:i A', strtotime($row->created_at)) : $row->cdate; ?></td>
                                        <td class="inquiry-row-actions">
                                            <a href="<?php echo base_url(); ?>dashboard/inquiry/view/<?php echo $row->inquiryid; ?>" class="btn btn-primary"><i class="material-icons">call_made</i> <?php echo $this->lang->line('dash_gpanel_view'); ?></a>
                                            <a href="<?php echo base_url(); ?>dashboard/inquiry/delete/<?php echo $row->inquiryid; ?>" class="btn btn-danger delete"><i class="material-icons">clear</i> <?php echo $this->lang->line('dash_gpanel_delete'); ?></a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
