import uuid from 'node-uuid';

const hasAttributeWithValue = (dom, attributeName, valueList) => {
  const value = dom.hasAttribute(attributeName) ? dom.getAttribute(attributeName) : null;
  return value && valueList.lastIndexOf(value) !== -1;
};


export class ContainerDOM {
  /**
   * @param attributeName
   * @return {ContainerDOM}
   */
  static fromAttributeName(attributeName)  {
    return new ContainerDOM(attributeName);
  }

  /**
   * @param {String} attributeName
   */
  constructor(attributeName) {
    this.attributeName = attributeName;
    this.selector =  ['[', attributeName, ']'].join('');
  }

  /**
   * Returns a list containing the conf attributes for each container
   *
   * @return {Object}
   * @param dom
   * @param attributeNameToPropName
   */
  extractContainerConfiguration = (dom, attributeNameToPropName) => {
    if (!this.isContainerElement(dom)) { return null; } // TODO is it better to throw error ?

    const attributes = {};
    for (let i = dom.attributes.length - 1; i >= 0; i--) {
      const attr = dom.attributes[i];
      if (this.isContainerAttribute(attr)) {
        attributes[attr.name] = attr.value;
      }
    }

    return attributeNameToPropName ? attributeNameToPropName(attributes) : attributes;
  };

  /**
   * Returns a list containing the conf attributes for each container
   *
   * @param nodeList
   * @param attributeNameToPropName
   * @return {Array}
   */
  extractConfigurationFromList = (nodeList, attributeNameToPropName) => nodeList
    .map(dom => this.extractContainerConfiguration(dom, attributeNameToPropName))
    .filter(configuration => !!configuration)
  ;

  /**
   * Checks if a dom element is a container element
   *
   * @param dom
   */
  isContainerElement = dom => dom && dom.hasAttribute(this.attributeName);

  /**
   * Checks if a dom element attribute is a container attribute
   *
   * @param domAttr
   * @return {boolean}
   */
  isContainerAttribute = (domAttr) => {
    const { attributeName } = this;
    const attributePrefix = `${attributeName}-`;
    const { name } = domAttr;

    return name === attributeName || name === 'id' || name.substr(0, attributePrefix.length) === attributePrefix;
  };

  /**
   * Assigns a new id only if the element is missing an id and is a valid widget element
   *
   * @param dom
   * @return {string|null}
   */
  ensureContainerId = (dom) => {
    if (!this.isContainerElement(dom)) {
      return null;
    }

    let id = dom.hasAttribute('id') ? dom.getAttribute('id') : null;
    if (id) { return null; }

    id = uuid.v4();
    dom.setAttribute('id', id);
    return id;
  };

  /**
   * Returns a list of all the newly assigned ids
   *
   * @param {Array} nodeList
   */
  ensureContainerIds = nodeList => nodeList.map(dom => this.ensureContainerId(dom)).filter(id => !!id);

  /**
   * @param {String} id
   * @param {Document} document
   */
  findContainerById = (id, document) => {
    const element = document.getElementById(id);
    if (!this.isContainerElement(element)) { return null; }

    return element;
  };

  /**
   * Returns a list of children DOM nodes which are valid container nodes
   *
   * @return {Array}
   * @param dom A DOM Node which might contain widget markup
   */
  findAll = dom => dom.querySelectorAll(this.selector);


  /**
   * Returns a list of children DOM nodes which are valid container nodes
   *
   * @param {Array} list - A list of DOM Nodes who might contain container nodes
   * @return {Array}
   */
  findAllFromList = (list) =>  {
    /**
     * @param {Array} containerList
     * @param {Array} found
     * @return {*}
     */
    const reducer = (containerList, found) => {
      if (!found.length) { return containerList; }

      containerList.push(...found);
      return containerList;
    };

    return list.map(dom => this.findAll(dom)).reduce(reducer, []);
  };

  findAllFromListByType = (list, typeList) => {
    const filter = dom => hasAttributeWithValue(dom, this.attributeName, typeList);
    return this.findAllFromList(list).filter(filter);
  };

}

