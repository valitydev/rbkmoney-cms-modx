RBKmoney.grid.TransactionsGrid = function (config) {
    config = config || {};
    Ext.apply(config, {
        url: RBKmoney.config.connector_url,
        baseParams: {
            action: 'transactions',
        },
        autoHeight: true,
        paging: true,
        fields: this.getFields(),
        columns: this.getColumns(),
    });
    RBKmoney.grid.TransactionsGrid.superclass.constructor.call(this, config);
};
Ext.extend(RBKmoney.grid.TransactionsGrid, MODx.grid.Grid, {
    getFields: function () {
        return [
            'id', 'product', 'status', 'amount', 'createdAt', 'firstAction', 'secondAction'
        ];
    },

    getColumns: function () {
        return [
            {
                header: _('RBK_MONEY_TRANSACTION_ID'),
                dataIndex: 'id',
                width: 5,
            },
            {
                header: _('RBK_MONEY_TRANSACTION_PRODUCT'),
                dataIndex: 'product',
                width: 50,
            },
            {
                header: _('RBK_MONEY_TRANSACTION_STATUS'),
                dataIndex: 'status',
                width: 15,
            },
            {
                header: _('RBK_MONEY_TRANSACTION_AMOUNT'),
                dataIndex: 'amount',
                width: 15,
            },
            {
                header: _('RBK_MONEY_TRANSACTION_CREATED_AT'),
                dataIndex: 'createdAt',
                width: 15,
            },
            {
                width: 17,
                renderer: function (val) {
                    if (Object.keys(val).length !== 0) {
                        return '<input class="x-btn" type="button" value="' + val.name + '""/>';
                    }
                },
                dataIndex: 'firstAction',
                listeners: {
                    click: {
                        fn: function () {
                            var data = this.getSelectionModel().getSelected().json.firstAction;

                            MODx.Ajax.request({
                                url: RBKmoney.config.connector_url,
                                params: {
                                    action: 'transactionActions',
                                    invoiceId: data.invoiceId,
                                    paymentId: data.paymentId,
                                    method: data.action,
                                },
                                listeners: {
                                    success: {
                                        fn: function (response) {
                                            MODx.msg.alert('', response.message);
                                            this.refresh();
                                        }, scope: this
                                    },
                                    failure: {
                                        fn: function (response) {
                                            MODx.msg.alert(_('RBK_MONEY_ERROR'), response.message);
                                        }, scope: this
                                    },
                                }
                            })

                        }, scope: this
                    }
                }
            },
            {
                width: 15,
                renderer: function (val) {
                    if (Object.keys(val).length !== 0) {
                        return '<input class="x-btn" type="button" value="' + val.name + '""/>';
                    }
                },
                dataIndex: 'secondAction',
                listeners: {
                    click: {
                        fn: function () {
                            var data = this.getSelectionModel().getSelected().json.secondAction;

                            MODx.Ajax.request({
                                url: RBKmoney.config.connector_url,
                                params: {
                                    action: 'transactionActions',
                                    invoiceId: data.invoiceId,
                                    paymentId: data.paymentId,
                                    method: data.action,
                                },
                                listeners: {
                                    success: {
                                        fn: function (response) {
                                            MODx.msg.alert('', response.message);
                                            this.refresh();
                                        }, scope: this
                                    },
                                    failure: {
                                        fn: function (response) {
                                            MODx.msg.alert(_('RBK_MONEY_ERROR'), response.message);
                                        }, scope: this
                                    },
                                }
                            })

                        }, scope: this
                    }
                }
            }
        ];
    },
});
Ext.reg('rbkmoney-grid-transactions', RBKmoney.grid.TransactionsGrid);