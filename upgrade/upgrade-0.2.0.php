<?php

function upgrade_module_0_2_0($module)
{
    return $module->registerHook('displayFooter')
        && $module->registerHook('displayOrderConfirmation');
}
