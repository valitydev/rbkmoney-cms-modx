RBKmoney.panel.RecurrentItems = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        id: 'rbkmoney-recurrent-items',
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
                        action: 'mgr/settings/getrecurrentitems',
                    },
                    listeners: {
                        'success': {
                            fn: function (r) {
                                var items = [];
                                var value = '';

                                for (var i = 0; i < r.results.length; i++) {
                                    if (value[2]) {
                                        value += "\n";
                                    }
                                    value += r.results[i].article
                                }

                                items.push({
                                    xtype: 'textarea',
                                    id: 'recurrentItems',
                                    fieldLabel: RBKmoney.config.langConst.RBK_MONEY_ITEM_IDS,
                                    name: 'recurrentItems',
                                    value: value,
                                    selectOnFocus: true,
                                    width: 300,
                                    minLength: 1,
                                    description: RBKmoney.config.langConst.RBK_MONEY_ITEM_IDS
                                });
                                this.add(items);
                                this.rendered = true;
                                this.doLayout();
                            }, scope: this
                        }
                    }
                });
            }
        }
        // Reset and Submit buttons
        , buttons: [
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
                                action: 'mgr/settings/updaterecurrentitems',
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
    RBKmoney.panel.RecurrentItems.superclass.constructor.call(this, config);
};
Ext.extend(RBKmoney.panel.RecurrentItems, MODx.FormPanel);
Ext.reg('rbkmoney-recurrent-items', RBKmoney.panel.RecurrentItems);