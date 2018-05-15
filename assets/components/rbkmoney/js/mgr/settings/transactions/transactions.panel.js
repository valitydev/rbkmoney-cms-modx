RBKmoney.panel.TransactionsForm = function (config) {
    config = config || {};
    Ext.apply(config, {
        layout: 'form',
        cls: 'main-wrapper',
        defaults: {msgTarget: 'under', border: false},
        anchor: '100% 100%',
        border: false,
        items: this.getFields(),
        listeners: this.getListeners(),
        buttons: this.getButtons(),
        keys: this.getKeys(),
    });
    RBKmoney.panel.TransactionsForm.superclass.constructor.call(this, config);
};
Ext.extend(RBKmoney.panel.TransactionsForm, MODx.FormPanel, {

    grid: null,

    getFields: function () {
        var date = new Date();
        return [
            {
                xtype: 'datefield',
                id: 'date_start',
                name: 'date_start',
                value: date.format('Y-m-d'),
                fieldLabel: RBKmoney.config.langConst.RBK_MONEY_DATE_FILTER_FROM,
                format: 'Y-m-d',
                listeners: {
                    select: {
                        fn: function () {
                            this.fireEvent('change');
                        }, scope: this
                    },
                },
            },
            {
                xtype: 'datefield',
                id: 'date_end',
                name: 'date_end',
                value: date.format('Y-m-d'),
                fieldLabel: RBKmoney.config.langConst.RBK_MONEY_DATE_FILTER_TO,
                format: 'Y-m-d',
                listeners: {
                    select: {
                        fn: function () {
                            this.fireEvent('change');
                        }, scope: this
                    },
                },
            }
        ];
    },

    getListeners: function () {
        return {
            beforerender: function () {
                this.grid = Ext.getCmp('rbkmoney-grid-transactions');
            },
            change: function () {
                this.submit();
            },
        }
    },

    getButtons: function () {
        return [{
            text: '<i class="icon icon-times"></i> ' + _('RBK_MONEY_RESET'),
            handler: this.reset,
            scope: this,
            iconCls: 'x-btn-small',
        }, {
            text: '<i class="icon icon-check"></i> ' + _('RBK_MONEY_FILTER_SUBMIT'),
            handler: this.submit,
            scope: this,
            cls: 'primary-button',
            iconCls: 'x-btn-small',
        }];
    },

    getKeys: function () {
        return [{
            key: Ext.EventObject.ENTER,
            fn: function () {
                this.submit();
            },
            scope: this
        }];
    },

    submit: function () {
        var store = this.grid.getStore();
        var form = this.getForm();

        var values = form.getFieldValues();
        for (var i in values) {
            if (i != undefined && values.hasOwnProperty(i)) {
                store.baseParams[i] = values[i];
            }
        }
        this.refresh();
    },

    reset: function () {
        var store = this.grid.getStore();
        var form = this.getForm();

        form.items.each(function (f) {
            if (f.name == 'status') {
                f.clearValue();
            }
            else {
                f.reset();
            }
        });

        var values = form.getValues();
        for (var i in values) {
            if (values.hasOwnProperty(i)) {
                store.baseParams[i] = '';
            }
        }
        this.refresh();
    },

    refresh: function () {
        this.grid.getBottomToolbar().changePage(1);
    },

    updateInfo: function () {
    },

    focusFirstField: function () {
    },

});
Ext.reg('rbkmoney-form-transactions', RBKmoney.panel.TransactionsForm);