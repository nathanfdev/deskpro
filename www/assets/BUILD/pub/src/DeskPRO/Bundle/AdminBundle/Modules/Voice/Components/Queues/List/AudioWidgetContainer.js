import React, { PropTypes } from 'react';
import $ from 'jquery';
import { connect } from 'react-redux';
import { updateQueue } from './../../../Actions/queueActions';

@connect()
class AudioWidgetContainer extends React.Component {

  static propTypes = {
    queue:    PropTypes.object,
    propName: PropTypes.string,
    children: PropTypes.node,
    dispatch: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      errors: {},
      saving: false
    };
  }

  onSubmit = (data) => {
    const { queue, propName, dispatch } = this.props;
    this.setState({
      errors: {},
      saving: true
    });

    const promise = dispatch(updateQueue(queue.get('id'), {
      [propName]: data
    }));

    promise.success(() => {
      this.setState({
        saving: false
      }, () => this.widget.onClose());
    });
    promise.error((result) => {
      const resultErrors = result.errors.fields[propName];
      const errors = $.extend(true, resultErrors, {
        fields: {
          blob: {
            fields: {
              [data.type]: resultErrors
            }
          }
        }
      });

      this.setState({
        errors,
        saving: false
      });
    });
  };

  render() {
    const { queue, propName, children } = this.props;

    return React.cloneElement(children, {
      ...children.props,
      ...this.state,

      ref:      (c) => { this.widget = c; },
      value:    queue.get(propName),
      onSubmit: this.onSubmit
    });
  }
}

export default AudioWidgetContainer;
