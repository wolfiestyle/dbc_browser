CPP = g++
REF = ./extract_names.pl
CPPFLAGS = -Wall -pipe
LIBS =
SRC = $(wildcard *.cpp)
GENSRC = DBCStructure.ref.cpp
OBJ = $(SRC:.cpp=.o) $(GENSRC:.cpp=.o)
DEP = $(SRC:.cpp=.dep)
OUT = $(shell basename `pwd`)

.PHONY: all debug release profile clean

all: debug

debug:   CPPFLAGS += -g
release: CPPFLAGS += -O2 -ffast-math -fomit-frame-pointer -DNDEBUG
profile: CPPFLAGS += -pg -O2

debug release profile: $(OUT)

$(OUT): $(OBJ)
	$(CPP) $(CPPFLAGS) $(OBJ) $(LIBS) -o $@

clean:
	rm -f *~ $(OBJ) $(DEP) $(OUT) a.out gmon.out

%.dep: %.cpp
	@set -e; rm -f $@; \
	$(CPP) -MM $(CPPFLAGS) $< | \
	sed 's,\($*\)\.o[ :]*,\1.o $@ : ,g' > $@

%.ref.cpp: %.h
	$(REF) $< > $@

-include $(DEP)
