class ContainerConfiguration
{
  /**
   * @param domNode
   * @return {ContainerConfiguration}
   */
  static fromDOM(domNode)
  {
    const configAttributesMap = {
      type: 'data-deskproapp',
      renderType: 'data-deskproapp-render',
      renderSidebarContainer: 'data-deskproapp-render-sidebar',
      renderIconsContainer: 'data-deskproapp-render-icons',
    };

    const configuration = Object.keys(configAttributesMap).reduce(function (configuration, key) {
      const attributeName = configAttributesMap[key];
      if (domNode.hasAttribute(attributeName)) {
        configuration[key] = domNode.getAttribute(attributeName);
      }
      return configuration;
    }, {});

    return ContainerConfiguration.fromJS(configuration);
  }

  /**
   * @param {object} config
   * @return {ContainerConfiguration}
   */
  static fromJS(config) {
    const { type, renderType, renderSidebarContainer, renderIconsContainer } = config;
    return new ContainerConfiguration(type, renderType, renderSidebarContainer, renderIconsContainer);
  }

  /**
   * @param {String} type
   * @param {String} renderType
   * @param {String} renderSidebarContainer
   * @param {String} renderIconsContainer
   */
  constructor(type, renderType, renderSidebarContainer, renderIconsContainer)
  {
    this.targetType = type;
    this.renderType = renderType;
    this.renderSidebarContainer = renderSidebarContainer;
    this.renderIconsContainer = renderIconsContainer;
  }
}

export default ContainerConfiguration;
