import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';

export class ListFrameContainer extends React.Component {

  static propTypes = {
    children:  PropTypes.node,
    className: PropTypes.string
  };

  render() {
    const { className, children } = this.props;

    return (
      <section className={classNames(className, 'dp-list-frame')}>
        <div className="feedback-list">
          {children}
        </div>
      </section>
    );
  }
}
