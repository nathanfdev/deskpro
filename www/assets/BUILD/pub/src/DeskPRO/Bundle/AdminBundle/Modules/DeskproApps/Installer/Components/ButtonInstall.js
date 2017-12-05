import PropTypes from 'prop-types';
import React from 'react';

const ButtonInstall = ({ onClick }) =>  // eslint-disable-line class-methods-use-this, no-unused-vars
   (<button className={'btn btn-install btn-success'} onClick={onClick}>Install App</button>);

ButtonInstall.propTypes = {
  onClick: PropTypes.func.isRequired
};

export { ButtonInstall };
