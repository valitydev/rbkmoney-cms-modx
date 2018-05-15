var RBKmoney = function (config) {
    config = config || {};
    RBKmoney.superclass.constructor.call(this, config);
};
Ext.extend(RBKmoney, Ext.Component, {
    page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}, view: {}, utils: {}, checkbox: {}, form: {}
});
Ext.reg('rbkmoney', RBKmoney);

RBKmoney = new RBKmoney();