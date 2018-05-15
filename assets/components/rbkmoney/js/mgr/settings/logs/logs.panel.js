RBKmoney.panel.Logs = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        id: 'rbkmoney-logs',
        autoHeight: true,
        paging: true,
        baseCls: 'modx-formpanel',
        width: 800,
        layout: 'form',
        bodyStyle: 'padding: 10px',
        border: true,
        renderTo: Ext.getBody(),
        items: [],
        listeners: {
            render: function () {
                MODx.Ajax.request({
                    url: RBKmoney.config.connector_url,
                    params: {
                        action: 'log',
                        method: 'show',
                    },
                    listeners: {
                        'success': {
                            fn: function (r) {
                                this.add({
                                    xtype: 'textarea',
                                    id: 'logs',
                                    name: 'logs',
                                    grow: true,
                                    growMax: 500,
                                    growMin: 500,
                                    style: {
                                        wrap: 'off'
                                    },
                                    value: r.message,
                                    width: 900,
                                    readOnly: true,
                                });
                                this.rendered = true;
                                this.doLayout();
                            }, scope: this
                        }
                    }
                });
            }
        },
        buttons: [
            {
                text: RBKmoney.config.langConst.RBK_MONEY_DELETE_LOGS,
                scope: this,
                handler: function () {
                    MODx.Ajax.request({
                        url: RBKmoney.config.connector_url,
                        params: {
                            action: 'log',
                            method: 'delete',
                        },
                        listeners: {
                            'success': {
                                fn: function () {
                                    var form = this.getForm();
                                    form.reset();
                                    var i, it = form.items.items, l = it.length;
                                    for (i = 0; i < l; i++) {
                                        it[i].setValue('');
                                    }
                                    Ext.Msg.alert(RBKmoney.config.langConst.RBK_MONEY_LOGS_DELETED, RBKmoney.config.langConst.RBK_MONEY_LOGS_DELETED);
                                }, scope: this
                            }
                        }
                    });
                }
            },
            {
                text: RBKmoney.config.langConst.RBK_MONEY_DOWNLOAD_LOGS,
                cls: "primary-button",
                scope: this,
                hrefTarget: '_blank',
                handler: function () {
                    Ext.DomHelper.append(document.body, {
                        tag: 'iframe',
                        src: RBKmoney.config.downloadLogsUrl,
                    });
                }
            }
        ],
    });
    RBKmoney.panel.Logs.superclass.constructor.call(this, config);
};
Ext.extend(RBKmoney.panel.Logs, MODx.FormPanel);
Ext.reg('rbkmoney-logs', RBKmoney.panel.Logs);