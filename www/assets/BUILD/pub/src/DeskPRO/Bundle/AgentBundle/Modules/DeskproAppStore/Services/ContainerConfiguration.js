const configPropertiesToAttributesMap = {
  type:                   'data-deskproapp',
  renderType:             'data-deskproapp-render',
  renderSidebarContainer: 'data-deskproapp-render-sidebar',
  renderIconsContainer:   'data-deskproapp-render-icons',
};

const defaultValues = {
  renderType: 'inplace'
};

class ContainerConfiguration {
  /**
   * @param domNode
   * @return {ContainerConfiguration}
   */
  static fromDOM(domNode)  {
    const attributesToPropsReducer = (configuration, key) => {
      const attributeName = configPropertiesToAttributesMap[key];
      if (domNode.hasAttribute(attributeName)) {
        configuration[key] = domNode.getAttribute(attributeName);
      }
      return configuration;
    };
    const configProps = Object.keys(configPropertiesToAttributesMap).reduce(attributesToPropsReducer, {});

    Object.keys(configPropertiesToAttributesMap).forEach((propWithDefaultVal) => {
      if (!Object.prototype.hasOwnProperty.call(configProps, propWithDefaultVal)) {
        configProps[propWithDefaultVal] = defaultValues[propWithDefaultVal];
      }
    });

    return ContainerConfiguration.fromJS(configProps);
  }

  /**
   * @param {object} config
   * @return {ContainerConfiguration}
   */
  static fromJS(config) {
    const { type, renderType, renderSidebarContainer, renderIconsContainer } = config;
    return new ContainerConfiguration({ type, renderType, renderSidebarContainer, renderIconsContainer });
  }

  /**
   * @param {String} type
   * @param {String} renderType
   * @param {String} renderSidebarContainer
   * @param {String} renderIconsContainer
   */
  constructor({ type, renderType, renderSidebarContainer, renderIconsContainer })  {
    this.targetType = type;
    this.renderType = renderType;
    this.renderSidebarContainer = renderSidebarContainer;
    this.renderIconsContainer = renderIconsContainer;
  }
}

export default ContainerConfiguration;
