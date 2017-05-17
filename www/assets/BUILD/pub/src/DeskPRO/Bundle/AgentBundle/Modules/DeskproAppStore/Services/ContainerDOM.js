import uuid from 'node-uuid';

export class ContainerDOM
{
  /**
   * @param attributeName
   * @return {ContainerDOM}
   */
  static fromAttributeName(attributeName)
  {
    return new ContainerDOM(attributeName);
  }

  /**
   * @param {String} attributeName
   */
  constructor(attributeName) {
    this.attributeName = attributeName;
  }

  /**
   * Returns a list containing the conf attributes for each container
   *
   * @param nodeList
   * @return {Array}
   */
  extractConfigAttributes = nodeList => {
    const { attributeName } = this;
    const attributePrefix = attributeName + '-';
    const confAttributes = [];

    nodeList.forEach(dom => {
      if (dom.hasAttribute(attributeName)) {
        const attributes = {};
        for(let i = dom.attributes.length - 1; i >= 0; i--) {
          const { name, value } = dom.attributes[i];
          if (name === attributeName || name === 'id' || name.substr(0, attributePrefix.length) === attributePrefix) {
            attributes[name] = value;
          }
        }
        confAttributes.push(attributes);
      }
    });

    return confAttributes;
  };

  /**
   * Returns a list of all container dom nodes which were assigned ids (did not have one before)
   *
   * @param {Array} nodeList
   */
  assignIds = nodeList =>
  {
    const { attributeName } = this;
    const assignedIds = [];

    nodeList.forEach(dom => {
      if (dom.hasAttribute(attributeName) && !dom.id) {
        dom.setAttribute('id', uuid.v4());
        assignedIds.push(dom);
      }
    });

    return assignedIds;
  };

  /**
   * @param {String} id
   * @param {Document} document
   */
  findById = (id, document) => {

    const element = document.getElementById(id);
    const { attributeName } = this;

    if (element && element.hasAttribute(attributeName)) {
      return element;
    }
    return null;
  };

  /**
   * Returns a list of children DOM nodes which are valid container nodes
   *
   * @param {Array} list - A list of DOM Nodes who might contain container nodes
   * @return {Array}
   */
  findAll = list =>
  {
    const selector = [ '[', this.attributeName, ']' ].join('');

    const containers = [];
    list.forEach(dom => containers.push.apply(containers, dom.querySelectorAll(selector)));

    return containers;
  };

  filterByTargetTypeList = (dom, targetTypeList) =>
  {
    return this.filterAllByTargetTypeList([ dom ], targetTypeList);
  };

  filterAllByTargetTypeList = (list, targetTypeList) =>
  {
    const acceptorFilter = this.createAcceptorFromTargetType(targetTypeList);
    return this.filterAll(list, acceptorFilter);
  };

  /**
   * @param {Array} list
   * @param {Function} filter
   * @return {Array}
   */
  filterAll = (list, filter) =>
  {
      const nodes = this.findAll(list);
      return nodes.filter(filter);
  };

  /**
   * Creates a filter that accepts a DOM node if it has a target attribute with a certain value from targetTypeList
   *
   * @param targetTypeList
   * @return {function}
   */
  createAcceptorFromTargetType = (targetTypeList) =>
  {
    const { attributeName } = this;
    return dom => {
      const target = dom.hasAttribute(attributeName) ? dom.getAttribute(attributeName) : null;
      return targetTypeList.lastIndexOf(target) !== -1;
    };
  };
}

