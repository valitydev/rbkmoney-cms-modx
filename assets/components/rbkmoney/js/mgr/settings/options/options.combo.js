RBKmoney.combo.Units = function (config) {
    config = config || {};
    var value = [];
    for (var i = 0; i < RBKmoney.config.boolValues.length; i++) {
        value[i] = [i, RBKmoney.config.boolValues[i]];
    }
    Ext.applyIf(config, {
        store: new Ext.data.ArrayStore({
            id: 0,
            fields: ['id', 'name'],
            data: value,
        }),
        mode: 'local',
        displayField: 'name',
        valueField: 'id'
    });
    RBKmoney.combo.Units.superclass.constructor.call(this, config);
};
Ext.extend(RBKmoney.combo.Units, MODx.combo.ComboBox);
Ext.reg('rbkmoney-combo-bool', RBKmoney.combo.Units);

RBKmoney.combo.Units = function (config) {
    config = config || {};
    var value = [];
    for (var i = 0; i < RBKmoney.config.paymentTypeValues.length; i++) {
        value[i] = [i, RBKmoney.config.paymentTypeValues[i]];
    }
    Ext.applyIf(config, {
        store: new Ext.data.ArrayStore({
            id: 0,
            fields: ['id', 'name'],
            data: value,
        }),
        mode: 'local',
        displayField: 'name',
        valueField: 'id'
    });
    RBKmoney.combo.Units.superclass.constructor.call(this, config);
};
Ext.extend(RBKmoney.combo.Units, MODx.combo.ComboBox);
Ext.reg('rbkmoney-combo-payment-type', RBKmoney.combo.Units);

RBKmoney.combo.Units = function (config) {
    config = config || {};
    var value = [];
    for (var i = 0; i < RBKmoney.config.holdExpirationValues.length; i++) {
        value[i] = [i, RBKmoney.config.holdExpirationValues[i]];
    }
    Ext.applyIf(config, {
        store: new Ext.data.ArrayStore({
            id: 0,
            fields: ['id', 'name'],
            data: value,
        }),
        mode: 'local',
        displayField: 'name',
        valueField: 'id'
    });
    RBKmoney.combo.Units.superclass.constructor.call(this, config);
};
Ext.extend(RBKmoney.combo.Units, MODx.combo.ComboBox);
Ext.reg('rbkmoney-combo-hold-expiration', RBKmoney.combo.Units);

RBKmoney.combo.Units = function (config) {
    config = config || {};
    var value = [];
    for (var i = 0; i < RBKmoney.config.fiscalizationValues.length; i++) {
        value[i] = [i, RBKmoney.config.fiscalizationValues[i]];
    }
    Ext.applyIf(config, {
        store: new Ext.data.ArrayStore({
            id: 0,
            fields: ['id', 'name'],
            data: value,
        }),
        mode: 'local',
        displayField: 'name',
        valueField: 'id'
    });
    RBKmoney.combo.Units.superclass.constructor.call(this, config);
};
Ext.extend(RBKmoney.combo.Units, MODx.combo.ComboBox);
Ext.reg('rbkmoney-combo-fiscalization', RBKmoney.combo.Units);

RBKmoney.combo.Units = function (config) {
    config = config || {};
    var value = [];
    for (var i = 0; i < RBKmoney.config.orderStatuses.length; i++) {
        value[i] = [RBKmoney.config.orderStatuses[i].id, RBKmoney.config.orderStatuses[i].name];
    }
    Ext.applyIf(config, {
        store: new Ext.data.ArrayStore({
            id: 0,
            fields: ['id', 'name'],
            data: value,
        }),
        mode: 'local',
        displayField: 'name',
        valueField: 'id'
    });
    RBKmoney.combo.Units.superclass.constructor.call(this, config);
};
Ext.extend(RBKmoney.combo.Units, MODx.combo.ComboBox);
Ext.reg('rbkmoney-combo-status', RBKmoney.combo.Units);

RBKmoney.combo.Units = function (config) {
    config = config || {};
    var value = [];
    for (var i = 0; i < RBKmoney.config.vatRate.length; i++) {
        value[i] = [i, RBKmoney.config.vatRate[i]];
    }
    Ext.applyIf(config, {
        store: new Ext.data.ArrayStore({
            id: 0,
            fields: ['id', 'name'],
            data: value,
        }),
        mode: 'local',
        displayField: 'name',
        valueField: 'id'
    });
    RBKmoney.combo.Units.superclass.constructor.call(this, config);
};
Ext.extend(RBKmoney.combo.Units, MODx.combo.ComboBox);
Ext.reg('rbkmoney-combo-vat', RBKmoney.combo.Units);

RBKmoney.combo.Units = function (config) {
    config = config || {};
    var value = [];
    for (var i = 0; i < RBKmoney.config.currency.length; i++) {
        value[i] = [i, RBKmoney.config.currency[i]];
    }
    Ext.applyIf(config, {
        store: new Ext.data.ArrayStore({
            id: 0,
            fields: ['id', 'name'],
            data: value,
        }),
        mode: 'local',
        displayField: 'name',
        valueField: 'id'
    });
    RBKmoney.combo.Units.superclass.constructor.call(this, config);
};
Ext.extend(RBKmoney.combo.Units, MODx.combo.ComboBox);
Ext.reg('rbkmoney-combo-currency', RBKmoney.combo.Units);
