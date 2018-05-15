Ext.onReady(function () {
    MODx.load({xtype: 'rbkmoney-page-settings'});
});

RBKmoney.page.Main = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        components: [{
            xtype: 'rbkmoney-panel-settings'
            , renderTo: 'rbkmoney-panel-settings-div'
        }]
    });
    RBKmoney.page.Main.superclass.constructor.call(this, config);
};
Ext.extend(RBKmoney.page.Main, MODx.Component);
Ext.reg('rbkmoney-page-settings', RBKmoney.page.Main);