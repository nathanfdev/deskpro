import React from 'react';
import Positioned from '../Positioned';

export default function positioned(isOpen,
                                   targetElement = null,
                                   position = null,
                                   positionAt = null,
                                   positionMy = null,
                                   collision = null,
                                   positionCalc = null,
                                   onOpen = null,
                                   onClose = null
                                  ) {
  return (DecoratedComponent) => {
    return class {
      render() {
        return (
          <Positioned isOpen={isOpen}
                      positionTarget={targetElement}
                      position={position}
                      positionAt={positionAt}
                      positionMy={positionMy}
                      collision={collision}
                      positionCalc={positionCalc}
                      onOpen={onOpen}
                      onClose={onClose}
                      >
            <DecoratedComponent {...this.props} />
          </Positioned>
        );
      }
    };
  };
}
