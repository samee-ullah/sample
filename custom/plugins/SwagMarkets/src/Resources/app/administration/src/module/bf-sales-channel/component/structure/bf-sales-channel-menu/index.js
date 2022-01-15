const {Component} = Shopware;
const FlatTree = Shopware.Helper.FlatTreeHelper;

Component.override('sw-sales-channel-menu', {
    name: 'bf-sales-channel-menu',
    computed: {
        buildMenuTree(){
            this.$super('buildMenuTree');
            const flatTree = new FlatTree();

            this.salesChannels.forEach((salesChannel) => {
                if (salesChannel.type.id !== '26a9ece25bd14b288b30c3d71e667d2c' && salesChannel.type.id !== '7ff39608fed04e4bbcc62710b7223966') {
                    if (typeof this.getDomainLink === "function") {
                        flatTree.add({
                            id: salesChannel.id,
                            path: 'sw.sales.channel.detail',
                            params: {id: salesChannel.id},
                            color: '#D8DDE6',
                            label: {label: salesChannel.translated.name, translated: true},
                            icon: salesChannel.type.iconName,
                            children: [],
                            domainLink: this.getDomainLink(salesChannel),
                            active: salesChannel.active
                        });
                    } else {
                        flatTree.add({
                            id: salesChannel.id,
                            path: 'sw.sales.channel.detail',
                            params: {id: salesChannel.id},
                            color: '#D8DDE6',
                            label: {label: salesChannel.translated.name, translated: true},
                            icon: salesChannel.type.iconName,
                            children: [],
                            active: salesChannel.active
                        });
                    }
                } else {
                    flatTree.add({
                        id: salesChannel.id,
                        path: 'bf.sales.channel.detail',
                        params: {id: salesChannel.id},
                        color: '#8a8f98',
                        label: {label: salesChannel.translated.name, translated: true},
                        icon: salesChannel.type.iconName,
                        children: [],
                        manufacturer: salesChannel.type.manufacturer
                    });
                }
            });

            return flatTree.convertToTree();
        }
    }
});
