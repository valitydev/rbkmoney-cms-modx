RBKmoney.panel.Settings = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        id: 'rbkmoney-form-settings',
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
            render: function (p) {
                MODx.Ajax.request({
                    url: RBKmoney.config.connector_url,
                    params: {
                        action: 'mgr/settings/getlist',
                    },
                    listeners: {
                        'success': {
                            fn: function (r) {
                                var name, code, value, id;
                                var items = [];
                                for (var i = 0; i < r.results.length; i++) {
                                    name = r.results[i].name;
                                    code = r.results[i].code;
                                    value = r.results[i].value;
                                    id = r.results[i].id;
                                    if ('apiKey' === code) {
                                        items.push({
                                            xtype: 'textarea',
                                            id: id,
                                            fieldLabel: RBKmoney.config.langConst[name],
                                            name: code,
                                            value: value,
                                            allowBlank: false,
                                            msgTarget: 'under',
                                            selectOnFocus: true,
                                            width: 300,
                                            minLength: 1,
                                            description: RBKmoney.config.langConst[name]
                                        });
                                        continue;
                                    }
                                    if (['shopId', 'successPageId', 'paymentPageId', 'callbackPageId'].indexOf(code) > -1) {
                                        items.push({
                                            xtype: 'textfield',
                                            id: id,
                                            fieldLabel: RBKmoney.config.langConst[name],
                                            name: code,
                                            value: value,
                                            allowBlank: false,
                                            msgTarget: 'under',
                                            selectOnFocus: true,
                                            width: 300,
                                            minLength: 1,
                                            maxLength: 255,
                                            description: RBKmoney.config.langConst[name]
                                        });
                                        continue;
                                    }
                                    if ('paymentType' === code) {
                                        items.push({
                                            xtype: 'rbkmoney-combo-payment-type',
                                            id: id,
                                            fieldLabel: RBKmoney.config.langConst[name],
                                            name: code,
                                            width: 300,
                                            allowBlank: false,
                                            msgTarget: 'under',
                                            description: RBKmoney.config.langConst[name],
                                            value: value,
                                        });
                                        continue;
                                    }
                                    if ('holdExpiration' === code) {
                                        items.push({
                                            xtype: 'rbkmoney-combo-hold-expiration',
                                            id: id,
                                            fieldLabel: RBKmoney.config.langConst[name],
                                            name: code,
                                            width: 300,
                                            allowBlank: false,
                                            msgTarget: 'under',
                                            description: RBKmoney.config.langConst[name],
                                            value: value,
                                        });
                                        continue;
                                    }
                                    if (['shadingCvv', 'cardHolder', 'saveLogs'].indexOf(code) > -1) {
                                        items.push({
                                            xtype: 'rbkmoney-combo-bool',
                                            id: id,
                                            fieldLabel: RBKmoney.config.langConst[name],
                                            name: code,
                                            width: 300,
                                            allowBlank: false,
                                            msgTarget: 'under',
                                            description: RBKmoney.config.langConst[name],
                                            value: value,
                                        });
                                        continue;
                                    }
                                    if ('fiscalization' === code) {
                                        items.push({
                                            xtype: 'rbkmoney-combo-fiscalization',
                                            id: id,
                                            fieldLabel: RBKmoney.config.langConst[name],
                                            name: code,
                                            width: 300,
                                            allowBlank: false,
                                            msgTarget: 'under',
                                            description: RBKmoney.config.langConst[name],
                                            value: value,
                                        });
                                        continue;
                                    }
                                    if (['successStatus', 'holdStatus', 'cancelStatus', 'refundStatus'].indexOf(code) > -1) {
                                        items.push({
                                            xtype: 'rbkmoney-combo-status',
                                            id: id,
                                            fieldLabel: RBKmoney.config.langConst[name],
                                            name: code,
                                            allowBlank: false,
                                            msgTarget: 'under',
                                            width: 300,
                                            description: RBKmoney.config.langConst[name],
                                            value: value,
                                        });
                                        continue;
                                    }
                                    if (['vatRate', 'deliveryVatRate'].indexOf(code) > -1) {
                                        items.push({
                                            xtype: 'rbkmoney-combo-vat',
                                            id: id,
                                            fieldLabel: RBKmoney.config.langConst[name],
                                            name: code,
                                            allowBlank: false,
                                            msgTarget: 'under',
                                            width: 300,
                                            description: RBKmoney.config.langConst[name],
                                            value: value,
                                        });
                                        continue;
                                    }
                                    if ('currency' === code) {
                                        items.push({
                                            xtype: 'rbkmoney-combo-currency',
                                            id: id,
                                            fieldLabel: RBKmoney.config.langConst[name],
                                            name: code,
                                            allowBlank: false,
                                            msgTarget: 'under',
                                            width: 300,
                                            description: RBKmoney.config.langConst[name],
                                            value: value,
                                        });
                                    }
                                }
                                this.add(items);
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
                text: RBKmoney.config.langConst.RBK_MONEY_RESET,
                scope: this,
                handler: function () {
                    var form = this.getForm();
                    form.reset();
                    var i, it = form.items.items, l = it.length;
                    for (i = 0; i < l; i++) {
                        it[i].setValue('');
                    }
                }
            },
            {
                text: RBKmoney.config.langConst.RBK_MONEY_SAVE,
                cls: "primary-button",
                scope: this,
                handler: function () {
                    var form = this.getForm();
                    var elem = this;
                    if (form.isValid()) {
                        form.submit({
                            url: RBKmoney.config.connector_url,
                            method: 'POST',
                            params: {
                                action: 'mgr/settings/updates',
                            },
                            root: 'results',
                            fields: ['id', 'name', 'value'],
                            success: function (form, action) {
                                Ext.Msg.alert(RBKmoney.config.langConst.RBK_MONEY_SAVED, RBKmoney.config.langConst.RBK_MONEY_SETTINGS_SAVED);
                                document.getElementById(elem.findParentByType('container').findParentByType('container').findParentByType('container').id).parentNode.scrollTop = 0;
                            },
                            failure: function (form, action) {
                                Ext.Msg.alert(RBKmoney.config.langConst.RBK_MONEY_ERROR, RBKmoney.config.langConst.RBK_MONEY_SETTINGS_SAVE_ERROR);
                                document.getElementById(elem.findParentByType('container').findParentByType('container').findParentByType('container').id).parentNode.scrollTop = 0;
                            }
                        });
                    }
                }
            }
        ],
    });
    RBKmoney.panel.Settings.superclass.constructor.call(this, config);
};
Ext.extend(RBKmoney.panel.Settings, MODx.FormPanel);
Ext.reg('rbkmoney-form-settings', RBKmoney.panel.Settings);