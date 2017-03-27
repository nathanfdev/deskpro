class ContainerDOMScanner
{
  /**
   * @param attributeName
   * @return {ContainerDOMScanner}
   */
  static fromAttributeName(attributeName)
  {
    return new ContainerDOMScanner(attributeName);
  }

  /**
   * @param {String} attributeName
   */
  constructor(attributeName) {
    this.attributeName = attributeName;
  }

  /**
   * Returns a list of children DOM nodes which are valid container nodes
   *
   * @param {Array} list - A list of DOM Nodes who might contain container nodes
   * @return {Array}
   */
  findAll = (list) =>
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

export default ContainerDOMScanner;

