const toNameAndPrefixedName = (prefix, name) => ([name, prefix + name.substr(0, 1).toUpperCase() + name.substr(1)]);
const copyPrefixedProps = (from, to, keyAndPrefixedKey) => {
  const [key, prefixedKey] = keyAndPrefixedKey;
  to[prefixedKey] = from[key];
  return to;
};

export class PropertyBag {
  constructor(props)  {
    this.props = { ...props };
  }

  getProp(name) {
    if (Object.prototype.hasOwnProperty.call(this.props, name)) {
      return this.props[name];
    }

    return null;
  }

  toJS(namePrefix) {
    let props;
    const unprefixedProps = Object.assign({}, { ...this.props });

    if (typeof namePrefix === 'string') {
      props = Object.keys(unprefixedProps)
        .map(toNameAndPrefixedName.bind(null, namePrefix))
        .reduce(copyPrefixedProps.bind(null, unprefixedProps), {})
      ;
    } else {
      props = unprefixedProps;
    }

    return JSON.parse(JSON.stringify(props));
  }
}
