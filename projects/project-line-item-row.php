<tr>
  <td>
    <input class="line-item-checkbox" type="checkbox" name="line-item-<?php echo htmlspecialchars($row["id"]) ?>-checkbox" onchange="toggleCheckbox()">
  </td>
  <td>
    <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($row["id"]) ?>">
    <input class="form-control" name="item_name" value="<?php echo htmlspecialchars($row["name"]) ?>" hx-post="/project-line-item/name.php" hx-trigger="keyup changed delay:500ms" hx-include="previous input">
  </td>
  <td>
    <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($row["id"]) ?>">
    <select class="form-select" name="item_status" hx-post="/project-line-item/status.php" hx-swap="none" hx-include="previous input">
      <option value="To Do" <?php if ($row["status"] == "To Do") echo "selected" ?>>
        To Do
      </option>
      <option value="In Progress" <?php if ($row["status"] == "In Progress") echo "selected" ?>>
        In Progress
      </option>
      <option value="Testing" <?php if ($row["status"] == "Testing") echo "selected" ?>>
        Testing
      </option>
      <option value="Done" <?php if ($row["status"] == "Done") echo "selected" ?>>
        Done
      </option>
      <option value="Blocked" <?php if ($row["status"] == "Blocked") echo "selected" ?>>
        Blocked
      </option>
    </select>
  </td>
  <td class="d-flex justify-content-end" style="width: 300px;">
    <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($row["id"]) ?>">
    <input class="form-control" style="width: 100px;" type="number" name="hours_logged" value="<?php echo htmlspecialchars($row["hours_logged"]) ?>" hx-post="/project-line-item/hours_logged.php" hx-trigger="keyup changed delay:200ms, change changed delay:200ms" hx-include="closest td" hx-target="next td">
    <input type="hidden" name="hourly_rate" value="<?php echo htmlspecialchars($hourly_rate); ?>">
  </td>
  <td id="line-item-<?php echo htmlspecialchars($row["id"]) ?>" align="right">
    <?php echo format_currency($hourly_rate * $row["hours_logged"]); ?>
  </td>
  <td>
  </td>
  <td>
    <div class="dropdown">
      <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        Options
      </button>
      <ul class="dropdown-menu">
        <li>
          <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($row["id"]); ?>">
          <a class="dropdown-item" hx-delete="/project-line-item/delete.php?id=<?php echo htmlspecialchars($row["id"]); ?>" hx-include="previous input" hx-target="closest tr" hx-swap="outerHTML" hx-confirm="Are you sure you want to delete this line item?">
            Delete
          </a>
        </li>
      </ul>
    </div>
  </td>
</tr>