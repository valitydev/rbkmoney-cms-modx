RBKmoney.panel.Main = function (config) {
    config = config || {};
    Ext.apply(config, {
        baseCls: 'modx-formpanel',
        layout: 'anchor',
        hideMode: 'offsets',
        flex: 1,
        items: [
            {
                html: '<h2>' + RBKmoney.config.langConst.RBK_MONEY + '</h2>',
                cls: '',
                style: {
                    margin: '15px 0'
                }
            },
            {
                xtype: 'modx-tabs',
                defaults: {
                    border: false,
                    autoHeight: true
                },
                border: true,
                hideMode: 'offsets',
                items: [
                    {
                        title: RBKmoney.config.langConst.RBK_MONEY_SETTINGS,
                        layout: 'anchor',
                        items: [
                            {
                                xtype: 'rbkmoney-form-settings',
                                cls: 'main-wrapper',
                            }
                        ]
                    },
                    {
                        title: RBKmoney.config.langConst.RBK_MONEY_TRANSACTIONS,
                        layout: 'anchor',
                        cls: 'main-wrapper',
                        items: [
                            {
                                xtype: 'rbkmoney-form-transactions',
                                id: 'rbkmoney-form-transactions',
                            },
                            {
                                xtype: 'rbkmoney-grid-transactions',
                                id: 'rbkmoney-grid-transactions',
                            },
                        ],
                    },
                    {
                        title: RBKmoney.config.langConst.RBK_MONEY_RECURRENT,
                        layout: 'anchor',
                        items: [
                            {
                                xtype: 'rbkmoney-recurrent',
                                cls: 'main-wrapper',
                            }
                        ]
                    },
                    {
                        title: RBKmoney.config.langConst.RBK_MONEY_RECURRENT_ITEMS,
                        layout: 'anchor',
                        items: [
                            {
                                xtype: 'rbkmoney-recurrent-items',
                                cls: 'main-wrapper',
                            }
                        ]
                    },
                    {
                        title: RBKmoney.config.langConst.RBK_MONEY_LOGS,
                        layout: 'anchor',
                        items: [
                            {
                                xtype: 'rbkmoney-logs',
                                cls: 'main-wrapper',
                            }
                        ]
                    },
                ]
            }]
    });
    RBKmoney.panel.Main.superclass.constructor.call(this, config);
};
Ext.extend(RBKmoney.panel.Main, MODx.Panel);
Ext.reg('rbkmoney-panel-settings', RBKmoney.panel.Main);