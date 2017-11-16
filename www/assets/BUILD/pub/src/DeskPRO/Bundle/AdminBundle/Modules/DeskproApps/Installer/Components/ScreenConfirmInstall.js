import PropTypes from 'prop-types';
import React from 'react';

const ScreenConfirmInstall = ({ iconUrl, title, description, version, onInstall }) => { // eslint-disable-line class-methods-use-this, no-unused-vars
  const appHeaderStyle = {
    marginLeft:  '15px',
    marginRight: '15px'
  };

  const titleStyle = {
    fontSize: '1.714rem'
  };

  const hrStyle = {
    marginLeft:   '15px',
    marginRight:  '15px',
    border:       0,
    borderBottom: '1px dotted #aaa',
    width:        'auto'
  };

  const appMainStyle = {
    margin: '15px'
  };

  const iconStyle = {
    height:      '64px',
    marginRight: '24px',
    float:       'left'
  };

  const descriptionStyle = {
    margin: '0 0 1em'
  };

  const versionStyle = {
    fontWeight: 'bold',
  };

  return (<div>
    <div className={'appHeader'} style={appHeaderStyle}>
      <img className={'icon'} style={iconStyle} src={iconUrl} alt={title} />
      <h2 style={titleStyle}>{title}</h2>
      <p style={descriptionStyle}>{description}</p>
      <data style={versionStyle}> v{version} </data>
    </div>
    <hr style={hrStyle} />
    <div className="appMain" style={appMainStyle}>
      <button className={'btn btn-install btn-success'} onClick={onInstall}>Install App</button>
    </div>
  </div>
  );
};

ScreenConfirmInstall.propTypes = {
  iconUrl:     PropTypes.string.isRequired,
  title:       PropTypes.string.isRequired,
  description: PropTypes.string.isRequired,
  version:     PropTypes.string.isRequired,
  onInstall:   PropTypes.func.isRequired
};

export { ScreenConfirmInstall };
