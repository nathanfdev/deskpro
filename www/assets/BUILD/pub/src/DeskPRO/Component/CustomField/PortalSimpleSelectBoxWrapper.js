import PropTypes from 'prop-types';
import React from 'react';
import PortalSimpleSelectBox from 'DeskPRO/Bundle/PortalBundle/React/Form/PortalSimpleSelectBox';

export default class PortalSimpleSelectBoxWrapper extends React.Component {

  static propTypes = {
    level:         PropTypes.number,
    multiple:      PropTypes.bool,
    value:         PropTypes.oneOfType([PropTypes.array, PropTypes.number]),
    choices:       PropTypes.object, // eslint-disable-line
    onChange:      PropTypes.func,
    widgetOptions: PropTypes.object
  };

  constructor(props) {
    super(props);
    this.state = {
      options: []
    };
  }

  componentWillReceiveProps(nextProps) {
    // prepare config for <PortalSimpleSelectBox> component
    // need to refactor <PortalSimpleSelectBox> to accept 'choices' as is
    const options = [];
    const prepareOptions = (choice, depth = 0) => {
      const hasChildren = choice.get('children') && choice.get('children').size > 0;
      const option = {
        id:    choice.get('id'),
        title: choice.get('title'),
        depth
      };

      if (nextProps.multiple) {
        option.children = hasChildren ? choice.get('children').map(child => ({
          id:    child.get('id'),
          title: child.get('title')
        })).toJS() : [];
      }

      options.push(option);

      if (nextProps.multiple && hasChildren) {
        choice.get('children').map(child => prepareOptions(child, depth + 1));
      }
    };

    nextProps.choices.map(choice => prepareOptions(choice));
    this.setState({ options });
  }

  onChange = (value) => {
    const { onChange } = this.props;

    if (Array.isArray(value)) {
      onChange(value.map(item => item.id));
    } else {
      onChange(value.id);
    }
  };

  render() {
    const { level, multiple, value = [], widgetOptions } = this.props;
    let selected;
    if (multiple) {
      selected = [];

      if (Array.isArray(value)) {
        value.forEach((id) => {
          this.state.options.forEach((option) => {
            if (option.id === id) {
              selected.push(option);
            }
          });
        });
      }
    } else {
      this.state.options.forEach((option) => {
        if (option.id === value) {
          selected = option;
        }
      });
    }

    return (
      <PortalSimpleSelectBox
        multiple={multiple}
        options={this.state.options}
        level={level}
        value={selected}
        widgetOptions={widgetOptions}
        onChange={this.onChange}
      />
    );
  }
}
