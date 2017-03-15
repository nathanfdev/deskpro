class ContainerDOMNodeSelector
{
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
   * Creates a filter that accepts a DOM node if it has a target attribute with a certain value
   *
   * @param targetTypeList
   * @return {function(*=)}
   */
  createAcceptorFromTargetType = (targetTypeList) =>
  {
    return dom => {
      const target = this.type(dom);
      return targetTypeList.lastIndexOf(target) !== -1;
    };
  };

  type = (domNode) =>
  {
    const { attributeName } = this;
    const target = domNode.getAttribute(attributeName);
    return target;
  }
}

export default ContainerDOMNodeSelector;

