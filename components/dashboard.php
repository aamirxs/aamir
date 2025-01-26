<?php
if (!defined('ADMIN_USER')) exit;

// Get system information
$disk_total = disk_total_space('/');
$disk_free = disk_free_space('/');
$disk_used = $disk_total - $disk_free;
$disk_percent = round(($disk_used / $disk_total) * 100);

$load = sys_getloadavg();
$memory = array();
if (file_exists('/proc/meminfo')) {
    $meminfo = file_get_contents('/proc/meminfo');
    preg_match_all('/^(.+?):[ \t]+(\d+)/m', $meminfo, $matches);
    $memory = array_combine($matches[1], $matches[2]);
}
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <!-- Disk Usage -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h3 class="text-xl font-bold mb-4">Disk Usage</h3>
        <div class="relative pt-1">
            <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-gray-200">
                <div style="width:<?php echo $disk_percent; ?>%" 
                     class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500">
                </div>
            </div>
            <div class="text-sm text-gray-600">
                Used: <?php echo round($disk_used / 1024 / 1024 / 1024, 2); ?> GB
                of <?php echo round($disk_total / 1024 / 1024 / 1024, 2); ?> GB
            </div>
        </div>
    </div>

    <!-- System Load -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h3 class="text-xl font-bold mb-4">System Load</h3>
        <div class="grid grid-cols-3 gap-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-500"><?php echo $load[0]; ?></div>
                <div class="text-sm text-gray-600">1 min</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-500"><?php echo $load[1]; ?></div>
                <div class="text-sm text-gray-600">5 min</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-500"><?php echo $load[2]; ?></div>
                <div class="text-sm text-gray-600">15 min</div>
            </div>
        </div>
    </div>

    <!-- Memory Usage -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h3 class="text-xl font-bold mb-4">Memory Usage</h3>
        <?php if (!empty($memory)): ?>
            <?php
            $mem_total = $memory['MemTotal'];
            $mem_free = $memory['MemFree'];
            $mem_used = $mem_total - $mem_free;
            $mem_percent = round(($mem_used / $mem_total) * 100);
            ?>
            <div class="relative pt-1">
                <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-gray-200">
                    <div style="width:<?php echo $mem_percent; ?>%" 
                         class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-green-500">
                    </div>
                </div>
                <div class="text-sm text-gray-600">
                    Used: <?php echo round($mem_used / 1024 / 1024, 2); ?> GB
                    of <?php echo round($mem_total / 1024 / 1024, 2); ?> GB
                </div>
            </div>
        <?php else: ?>
            <div class="text-gray-600">Memory information unavailable</div>
        <?php endif; ?>
    </div>
</div> 