<?php declare(strict_types=1); ?>
<div class="panel">
    <h2><?= $isEdit ? 'Edit Group Assignments' : 'Create Group & Assign Rules' ?></h2>
    <?php if ($isEdit && $group === null): ?>
        <p class="error">Group not found.</p>
    <?php else: ?>
        <form method="post">
            <input type="hidden" name="form_action" value="<?= $isEdit ? 'edit_group' : 'create_group' ?>">
            <?php if ($isEdit): ?>
                <input type="hidden" name="group_id" value="<?= (int)$group->id ?>">
                <p><strong>Group:</strong> <?= ViewHelper::h($group->name) ?></p>
            <?php else: ?>
                <label>Group Name:
                    <input type="text" name="group_name" required>
                </label>
            <?php endif; ?>

            <p class="small">Constraint: max 3 tiers, decision cannot have children, condition must have children, same rule cannot repeat under same parent.</p>

            <table id="rows-table">
                <thead>
                <tr><th>Row Key</th><th>Rule</th><th>Parent</th><th>Remove</th></tr>
                </thead>
                <tbody id="rows-body"></tbody>
            </table>
            <p>
                <button type="button" id="add-row">Add Row</button>
                <button type="submit">Save Assignments</button>
            </p>
        </form>
    <?php endif; ?>
</div>

<script>
    const rules = <?= json_encode(array_map(static fn(Rule $r) => ['id' => $r->id, 'name' => $r->name, 'type' => $r->type], $rules), JSON_THROW_ON_ERROR) ?>;
    const existing = <?= json_encode(array_map(static fn(Assignment $a) => ['key' => (string)$a->id, 'rule_id' => $a->ruleId, 'parent_key' => $a->parentAssignmentId === null ? '' : (string)$a->parentAssignmentId], $assignmentRows), JSON_THROW_ON_ERROR) ?>;

    $(function () {
        const $body = $('#rows-body');
        let counter = 1000;

        function buildRuleOptions(selectedId) {
            return rules.map(function (rule) {
                const selected = String(rule.id) === String(selectedId) ? 'selected' : '';
                return `<option value="${rule.id}" ${selected}>${rule.name} [${rule.type}]</option>`;
            }).join('');
        }

        function rebuildParentOptions() {
            const rowKeys = $body.find('tr[data-key]').map(function () {
                return $(this).data('key').toString();
            }).get();

            $body.find('select[data-parent]').each(function () {
                const $select = $(this);
                const current = ($select.val() || '').toString();
                const selfKey = ($select.data('self') || '').toString();
                const options = ['<option value="">ROOT</option>']
                    .concat(rowKeys.filter(function (k) { return k !== selfKey; }).map(function (k) {
                        return `<option value="${k}">${k}</option>`;
                    }));
                $select.html(options.join(''));
                $select.val(rowKeys.includes(current) || current === '' ? current : '');
            });
        }

        function addRow(key, ruleId, parentKey) {
            const selectedRuleId = ruleId || '';
            const selectedParentKey = parentKey || '';
            const rowHtml = `
                <tr data-key="${key}">
                    <td>${key}</td>
                    <td>
                        <select name="assignments[${key}][rule_id]" required>
                            <option value="">Select rule</option>
                            ${buildRuleOptions(selectedRuleId)}
                        </select>
                    </td>
                    <td>
                        <select name="assignments[${key}][parent_key]" data-parent data-self="${key}">
                            <option value="">ROOT</option>
                        </select>
                    </td>
                    <td><button type="button" data-remove>Delete</button></td>
                </tr>
            `;
            $body.append(rowHtml);
            rebuildParentOptions();
            $body.find(`select[data-parent][data-self="${key}"]`).val(selectedParentKey);
        }

        $('#add-row').on('click', function () {
            addRow(String(counter++), '', '');
        });

        $body.on('click', '[data-remove]', function () {
            $(this).closest('tr').remove();
            rebuildParentOptions();
        });

        if (existing.length > 0) {
            existing.forEach(function (item) {
                addRow(item.key, item.rule_id, item.parent_key);
            });
        } else {
            addRow('1', '', '');
        }
    });
</script>

