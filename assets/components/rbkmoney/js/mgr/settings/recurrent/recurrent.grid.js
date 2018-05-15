RBKmoney.grid.Recurrent = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        url: RBKmoney.config.connector_url,
        baseParams: {
            action: 'mgr/settings/getrecurrent',
        },
        autoHeight: true,
        paging: true,
        fields: this.getFields(),
        columns: this.getColumns(),
    });
    RBKmoney.grid.Recurrent.superclass.constructor.call(this, config);
};
Ext.extend(RBKmoney.grid.Recurrent, MODx.grid.Grid, {

    getFields: function () {
        return [
            'userName', 'amount', 'name', 'status', 'date', 'id'
        ];
    },

    getColumns: function () {
        return [
            {
                header: _('RBK_MONEY_USER_FIELD'),
                dataIndex: 'userName',
                width: 10,
            },
            {
                header: _('RBK_MONEY_AMOUNT_FIELD'),
                dataIndex: 'amount',
                width: 10,
            },
            {
                header: _('RBK_MONEY_TRANSACTION_PRODUCT'),
                dataIndex: 'name',
                width: 50,
            },
            {
                header: _('RBK_MONEY_TRANSACTION_STATUS'),
                dataIndex: 'status',
                width: 10,
            },
            {
                header: _('RBK_MONEY_RECURRENT_CREATE_DATE'),
                dataIndex: 'date',
                width: 10,
            },
            {
                width: 6,
                renderer: function(val){
                    return '<input class="x-btn" type="button" value="'+RBKmoney.config.langConst.RBK_MONEY_FORM_BUTTON_DELETE+'" name="'+val+'" id="delete"/>';
                },
                dataIndex: 'id',
                listeners: {
                    click: {
                        fn: function () {
                            var id = this.getSelectedAsList();

                            MODx.Ajax.request({
                                url: RBKmoney.config.connector_url,
                                params: {
                                    action: 'mgr/settings/removerecurrent',
                                    id: id,
                                },
                                listeners: {
                                    success: {
                                        fn: function () {
                                            this.refresh();
                                        }, scope: this
                                    },
                                    failure: {
                                        fn: function (response) {
                                            MODx.msg.alert(_('error'), response.message);
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
Ext.reg('rbkmoney-recurrent', RBKmoney.grid.Recurrent);