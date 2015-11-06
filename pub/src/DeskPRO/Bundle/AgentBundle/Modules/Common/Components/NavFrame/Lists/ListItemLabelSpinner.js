import React from 'react';
import Loader from 'react-loader';

export class ListItemLabelSpinner extends React.Component {

  render() {
    return (
      <Loader loaded={false}
              scale={0.35}
              left="50%"
              top="50%"
              color="green"
              width={3}
              component="span" />
    );
  }
}
