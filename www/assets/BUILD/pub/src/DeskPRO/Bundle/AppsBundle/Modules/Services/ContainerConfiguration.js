import { PropertyBag } from '../Domain';

const configPropertiesToAttributesMap = {
  type:       'data-deskproapp',
  renderType: 'data-deskproapp-render'
};

const defaultValues = {
  renderType: 'inplace'
};

class ContainerConfiguration extends PropertyBag {
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
  static fromJS(config) { return new ContainerConfiguration(config); }

  /**
   * @param {String} type
   * @param {String} renderType
   * @param {String} renderSidebarContainer
   * @param {String} renderIconsContainer
   * @param {Object} undeclaredProps
   */
  constructor({ type, renderType, renderSidebarContainer, renderIconsContainer, ...undeclaredProps })  {
    super({ targetType: type, renderType, renderSidebarContainer, renderIconsContainer, ...undeclaredProps });
  }

  get targetType() { return this.props.targetType; }

  get renderType() { return this.props.renderType; }

  get renderSidebarContainer() { return this.props.renderSidebarContainer; }

  get renderIconsContainer() { return this.props.renderIconsContainer; }
}

export { ContainerConfiguration };
