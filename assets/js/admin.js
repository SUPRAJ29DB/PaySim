/**
 * PaySim - Administrator Portal JavaScript
 */

async function toggleFreezeUser(userId, currentStatus) {
    const isFrozen = (currentStatus === 'frozen');
    const action = isFrozen ? 'unfreeze' : 'freeze';
    if (!confirm(`Are you sure you want to ${action} user #${userId}?`)) {
        return;
    }

    const res = await apiRequest('../api/admin/freeze-user.php', {
        method: 'POST',
        body: JSON.stringify({ user_id: userId, action })
    });

    if (res.success) {
        alert(res.message);
        window.location.reload();
    } else {
        alert('Action failed: ' + res.message);
    }
}
